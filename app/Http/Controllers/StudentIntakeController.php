<?php

namespace App\Http\Controllers;

use App\ComplaintStatusLog;
use App\Helpers\ActivityLogger;
use App\Service;
use App\Patient;
use App\StudentComplaint;
use App\User;
use App\CommonComplaintOption;
use App\ClinicQueue;
use Illuminate\Http\File;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\DB;
use App\Services\ClinicNotificationService;

class StudentIntakeController extends Controller
{
    public function dashboard(Request $request)
    {
        $student = $request->user()->student()->with('user')->firstOrFail();
        $currentComplaint = $student->complaints()->latest('submitted_at')->first();
        $clinicStaff = User::query()
            ->where('status', 'Active')
            ->whereHas('role', function ($query) {
                $query->whereIn('name', ['Doctor', 'Nurse', 'Staff']);
            })
            ->orderBy('first_name')
            ->orderBy('last_name')
            ->get();
        $services = Service::query()
            ->where('status', 'Active')
            ->orderBy('name')
            ->get();
        $account = $request->user()->ensurePatientAccount();
        $activeQueue = $account ? ClinicQueue::whereIn('patient_account_id', $account->accessibleAccountIds())
            ->whereIn('status', ['waiting','called','serving'])->latest('id')->first() : null;
        $assessment = $account ? $account->latestAssessment()->first() : null;
        $dependents = $account && in_array($account->patient_type, ['student','faculty']) ? $account->dependents()->latest()->get() : collect();
        $complaintOptions = CommonComplaintOption::where('is_active', true)->orderBy('category')->orderBy('display_order')->get()->groupBy('category');

        return view('student.dashboard', compact('student', 'currentComplaint', 'clinicStaff', 'services', 'account', 'activeQueue', 'assessment', 'dependents', 'complaintOptions'));
    }

    public function index(Request $request)
    {
        $student = $request->user()->student()->with('user')->firstOrFail();
        $complaints = $student->complaints()->with('queues')
            ->latest('submitted_at')
            ->paginate(10);

        $complaintOptions = CommonComplaintOption::where('is_active', true)->orderBy('category')->orderBy('display_order')->get()->groupBy('category');
        $dependents = $request->user()->patientAccount ? $request->user()->patientAccount->dependents()->where('verification_status', 'verified')->get() : collect();
        return view('student.complaints.index', compact('student', 'complaints', 'complaintOptions', 'dependents'));
    }

    public function profile(Request $request)
    {
        $student = $request->user()->student()->with('user')->firstOrFail();
        $account = $request->user()->ensurePatientAccount();

        return view('student.profile', compact('student', 'account'));
    }

    public function medicalHistory(Request $request)
    {
        $student = $request->user()->student()->firstOrFail();
        $patient = Patient::with([
            'medicalRecords.counterService.handler',
            'medicalRecords.consultation.doctor',
            'medicalRecords.prescription.doctor',
            'medicalRecords.attendingStaff',
        ])->where('id_number', $student->student_id_number)->first();

        $records = $patient
            ? $patient->medicalRecords->sortByDesc(function ($record) {
                return $record->date_of_consultation
                    ? $record->date_of_consultation->format('Y-m-d') . ' ' . ($record->time_of_consultation ?: '')
                    : $record->created_at;
            })
            : collect();

        return view('student.medical_history', compact('student', 'patient', 'records'));
    }

    public function store(Request $request)
    {
        $student = $request->user()->student()->with('user')->firstOrFail();
        if (! $request->filled('complaint_options') && $request->filled('chief_complaint')) {
            $legacyOption = CommonComplaintOption::where('is_active', true)->where('name', $request->input('chief_complaint'))->value('id');
            if ($legacyOption) $request->merge(['complaint_options'=>[$legacyOption]]);
        }
        $data = $request->validate([
            'complaint_options' => 'required|array|min:1',
            'complaint_options.*' => 'integer|exists:common_complaint_options,id',
            'other_complaint' => 'nullable|string|max:1000',
            'symptoms_description' => 'nullable|string|max:5000',
            'dependent_id' => 'nullable|integer',
            'attachment' => 'nullable|file|max:5120|mimes:jpg,jpeg,png,pdf,doc,docx',
        ]);
        $options = CommonComplaintOption::whereIn('id', $data['complaint_options'])->where('is_active', true)->get();
        abort_unless($options->count() === count(array_unique($data['complaint_options'])), 422, 'An unavailable complaint option was selected.');
        if ($options->contains('requires_details', true)) {
            $request->validate(['other_complaint'=>'required|string|max:1000']);
        }
        $account = $request->user()->patientAccount;
        $dependent = null;
        if (! empty($data['dependent_id'])) {
            $dependent = $account->dependents()->whereKey($data['dependent_id'])->where('verification_status', 'verified')->firstOrFail();
        }

        $attachment = null;
        if ($request->hasFile('attachment') && $request->file('attachment')->isValid()) {
            $attachment = Storage::disk('local')->putFile('patient-intake', new File($request->file('attachment')));
        }

        $complaint = DB::transaction(function () use ($student,$account,$dependent,$options,$data,$attachment,$request) {
        $complaint = StudentComplaint::create([
            'student_id' => $student->id,
            'student_id_number' => $student->student_id_number,
            'student_name' => $student->full_name,
            'patient_account_id' => $dependent && $dependent->patient_account_id ? $dependent->patient_account_id : $account->id,
            'dependent_id' => optional($dependent)->id,
            'complaint_category' => $options->pluck('category')->unique()->implode(', '),
            'chief_complaint' => $options->pluck('name')->implode(', '),
            'other_complaint' => $data['other_complaint'] ?? null,
            'symptoms_description' => $data['symptoms_description'] ?? '',
            'urgency_level' => 'Unassigned',
            'triage_priority' => 'unassigned',
            'status' => 'Pending',
            'attachment' => $attachment,
            'submitted_at' => now(),
        ]);
        $complaint->complaintOptions()->sync($options->pluck('id'));

        ComplaintStatusLog::create([
            'student_complaint_id' => $complaint->id,
            'changed_by' => $request->user()->id,
            'to_status' => 'Pending',
            'notes' => 'Complaint submitted by patient portal user; triage unassigned.',
        ]);

        ActivityLogger::log('submitted chief complaint (' . $student->full_name . ')');
        app(ClinicNotificationService::class)->newComplaint($complaint);
        return $complaint;
        });

        return redirect()->route('student.complaints.index')
            ->with('success', 'Your chief complaint was submitted to the clinic queue.');
    }

    public function show(Request $request, StudentComplaint $complaint)
    {
        abort_unless($complaint->student_id === optional($request->user()->student)->id, 403);

        return view('student.complaints.show', compact('complaint'));
    }

    public function attachment(Request $request, StudentComplaint $complaint)
    {
        $account = optional($request->user())->patientAccount;
        $role = optional(optional($request->user())->role)->name;
        $owns = $account && ($complaint->patient_account_id === $account->id || $account->dependents()->whereKey($complaint->dependent_id)->exists());
        abort_unless($owns || in_array($role, ['Administrator','Nurse','Doctor','Staff'], true), 403);
        abort_unless($complaint->attachment, 404);
        if (strpos($complaint->attachment, '/storage/') === 0) {
            return redirect($complaint->attachment);
        }
        abort_unless(Storage::disk('local')->exists($complaint->attachment), 404);
        return Storage::disk('local')->download($complaint->attachment);
    }
}
