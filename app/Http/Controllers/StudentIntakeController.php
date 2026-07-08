<?php

namespace App\Http\Controllers;

use App\ComplaintStatusLog;
use App\Helpers\ActivityLogger;
use App\Service;
use App\Patient;
use App\StudentComplaint;
use App\User;
use Illuminate\Http\File;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;

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

        return view('student.dashboard', compact('student', 'currentComplaint', 'clinicStaff', 'services'));
    }

    public function index(Request $request)
    {
        $student = $request->user()->student()->with('user')->firstOrFail();
        $complaints = $student->complaints()
            ->latest('submitted_at')
            ->paginate(10);

        return view('student.complaints.index', compact('student', 'complaints'));
    }

    public function profile(Request $request)
    {
        $student = $request->user()->student()->with('user')->firstOrFail();

        return view('student.profile', compact('student'));
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
        $data = $request->validate([
            'complaint_category' => 'required|string|max:100',
            'chief_complaint' => 'required|string|max:255',
            'symptoms_description' => 'required|string|max:5000',
            'urgency_level' => 'required|in:Low,Moderate,High',
            'attachment' => 'nullable|file|max:5120|mimes:jpg,jpeg,png,pdf,doc,docx',
        ]);

        $attachment = null;
        if ($request->hasFile('attachment') && $request->file('attachment')->isValid()) {
            $path = Storage::disk('public')->putFile('student-intake', new File($request->file('attachment')), 'public');
            $attachment = Storage::url($path);
        }

        $complaint = StudentComplaint::create([
            'student_id' => $student->id,
            'student_id_number' => $student->student_id_number,
            'student_name' => $student->full_name,
            'complaint_category' => $data['complaint_category'],
            'chief_complaint' => $data['chief_complaint'],
            'symptoms_description' => $data['symptoms_description'],
            'urgency_level' => $data['urgency_level'],
            'status' => 'Pending',
            'attachment' => $attachment,
            'submitted_at' => now(),
        ]);

        ComplaintStatusLog::create([
            'student_complaint_id' => $complaint->id,
            'changed_by' => $request->user()->id,
            'to_status' => 'Pending',
            'notes' => 'Complaint submitted by student.',
        ]);

        ActivityLogger::log('submitted chief complaint (' . $student->full_name . ')');

        return redirect()->route('student.complaints.index')
            ->with('success', 'Your chief complaint was submitted to the clinic queue.');
    }

    public function show(Request $request, StudentComplaint $complaint)
    {
        abort_unless($complaint->student_id === optional($request->user()->student)->id, 403);

        return view('student.complaints.show', compact('complaint'));
    }
}
