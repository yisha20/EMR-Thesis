<?php

namespace App\Http\Controllers;

use App\Consultation;
use App\MedicalCertificate;
use App\Models\ActivityLog;
use App\PatientAccount;
use App\Services\ClinicNotificationService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Storage;
use Illuminate\Validation\ValidationException;
use PDF;
use Throwable;

class MedicalCertificateController extends Controller
{
    public function create(Request $request, Consultation $consultation)
    {
        abort_unless((int) $consultation->doctor_id === (int) $request->user()->id, 403);
        abort_unless($consultation->completed_at, 422, 'Complete the doctor assessment first.');

        $existing = MedicalCertificate::where('consultation_id', $consultation->id)
            ->whereIn('status', ['draft', 'issued'])
            ->latest('id')
            ->first();

        if ($existing) {
            return redirect()->route(
                $existing->status === 'issued' ? 'medical-certificates.show' : 'medical-certificates.edit',
                $existing
            );
        }

        return view('certificates.form', [
            'certificate' => new MedicalCertificate,
            'consultation' => $consultation->load(['patient', 'doctor', 'complaint']),
        ]);
    }

    public function store(Request $request, Consultation $consultation)
    {
        abort_unless((int) $consultation->doctor_id === (int) $request->user()->id, 403);
        abort_unless($consultation->completed_at, 422);
        $data = $this->validated($request);

        $certificate = DB::transaction(function () use ($request, $consultation, $data) {
            $patient = $consultation->patient;
            $doctor = $request->user();
            $profile = $doctor->doctorProfile;
            $signatureVersion = $profile && $profile->signature_status === 'verified'
                ? $profile->signature_version
                : null;

            return MedicalCertificate::firstOrCreate(
                ['consultation_id' => $consultation->id, 'status' => 'draft'],
                array_merge($data, [
                    'certificate_number' => $this->number(),
                    'patient_id' => $patient->id,
                    'issued_by_doctor_id' => $doctor->id,
                    'issue_date' => today(),
                    'patient_name_snapshot' => trim($patient->first_name.' '.$patient->middle_name.' '.$patient->last_name),
                    'patient_id_snapshot' => $patient->id_number,
                    'age_snapshot' => $patient->age,
                    'sex_snapshot' => $patient->gender,
                    'address_snapshot' => $patient->present_address ?: $patient->home_address,
                    'doctor_name_snapshot' => $doctor->fullName(),
                    'doctor_license_number_snapshot' => optional($profile)->prc_number ?: $doctor->license_number,
                    'signature_version' => $signatureVersion,
                ])
            );
        });

        return redirect()->route('medical-certificates.edit', $certificate)->with('success', 'Draft saved. Review it before issuing.');
    }

    public function edit(Request $request, MedicalCertificate $medicalCertificate)
    {
        $this->doctorOwns($request, $medicalCertificate);
        abort_if($medicalCertificate->status !== 'draft', 422, 'Issued certificates are immutable.');

        return view('certificates.form', [
            'certificate' => $medicalCertificate,
            'consultation' => $medicalCertificate->consultation->load(['patient', 'doctor', 'complaint']),
        ]);
    }

    public function update(Request $request, MedicalCertificate $medicalCertificate)
    {
        $this->doctorOwns($request, $medicalCertificate);
        abort_if($medicalCertificate->status !== 'draft', 422, 'Issued certificates are immutable.');
        $medicalCertificate->update($this->validated($request));

        return back()->with('success', 'Draft saved. Review it before issuing.');
    }

    public function issue(Request $request, MedicalCertificate $medicalCertificate)
    {
        $this->doctorOwns($request, $medicalCertificate);
        $request->validate(['confirm_issue' => 'accepted']);
        abort_if($medicalCertificate->status !== 'draft', 422, 'Certificate already finalized.');
        $this->assertIssuable($medicalCertificate);

        DB::transaction(function () use ($request, $medicalCertificate) {
            $certificate = MedicalCertificate::whereKey($medicalCertificate->id)->lockForUpdate()->firstOrFail();
            abort_if($certificate->status !== 'draft', 422, 'Certificate already finalized.');
            $this->assertIssuable($certificate);
            $certificate->update(['status' => 'issued', 'issued_at' => now(), 'issue_date' => today()]);

            $account = PatientAccount::where('patient_id', $certificate->patient_id)->first();
            if ($account) {
                $notifications = app(ClinicNotificationService::class);
                $notifications->sendToUser(
                    $notifications->patientRecipient($account),
                    'medical_certificate_issued',
                    'Medical Certificate Available',
                    'Your medical certificate is available in your secure health record.',
                    ['patient_id' => $certificate->patient_id, 'action_url' => route('medical-certificates.show', $certificate)]
                );
            }

            ActivityLog::create([
                'user_id' => $request->user()->id,
                'action' => 'Medical certificate issued',
                'description' => $certificate->certificate_number,
            ]);
        });

        return redirect()->route('medical-certificates.show', $medicalCertificate)->with('success', 'Medical certificate issued.');
    }

    public function show(Request $request, MedicalCertificate $medicalCertificate)
    {
        $this->canView($request, $medicalCertificate);
        $this->canViewDraft($request, $medicalCertificate);

        return view('certificates.show', $this->documentData($medicalCertificate));
    }

    public function print(Request $request, MedicalCertificate $medicalCertificate)
    {
        $this->canView($request, $medicalCertificate);
        abort_unless($medicalCertificate->status === 'issued', 403);

        return view('certificates.print', $this->documentData($medicalCertificate));
    }

    public function pdf(Request $request, MedicalCertificate $medicalCertificate)
    {
        $this->canView($request, $medicalCertificate);
        abort_unless($medicalCertificate->status === 'issued', 403);

        if (! extension_loaded('gd')) {
            Log::error('Medical certificate PDF unavailable: PHP GD is not loaded.', ['certificate_id' => $medicalCertificate->id]);
            return redirect()->route('medical-certificates.show', $medicalCertificate)->with(
                'pdf_error',
                'Medical Certificate PDF could not be generated because the server is missing a required image-processing component. Please contact the system administrator.'
            );
        }

        try {
            $filename = 'Medical-Certificate-'.preg_replace('/[^A-Za-z0-9_-]/', '-', $medicalCertificate->certificate_number).'.pdf';
            return PDF::loadView('certificates.pdf', $this->documentData($medicalCertificate))
                ->setPaper('a4', 'portrait')
                ->download($filename);
        } catch (Throwable $exception) {
            report($exception);
            return redirect()->route('medical-certificates.show', $medicalCertificate)->with(
                'pdf_error',
                'Medical Certificate PDF could not be generated. The certificate remains safely stored. Please try again or contact the system administrator.'
            );
        }
    }

    private function validated(Request $request)
    {
        $data = $request->validate([
            'reason_for_visit' => 'required|string|max:2000',
            'consultation_performed' => 'nullable|boolean',
            'physical_examination_performed' => 'nullable|boolean',
            'clinical_impression' => 'required|string|max:3000',
            'fitness_status' => 'required|in:physically_fit,physically_unfit,fit_with_restrictions,other',
            'fitness_details' => 'nullable|required_if:fitness_status,fit_with_restrictions,other|string|max:2000',
            'purpose' => 'required|in:ojt,scholarship_application,employment,school_requirement,sports_activity,return_to_school,travel_requirement,other',
            'purpose_other' => 'nullable|required_if:purpose,other|string|max:255',
            'remarks' => 'nullable|required_if:fitness_status,physically_unfit|string|max:3000',
            'valid_from' => 'nullable|date',
            'valid_until' => 'nullable|date|after_or_equal:valid_from',
        ]);

        if (in_array(strtolower(trim($data['clinical_impression'])), ['none', 'n/a', 'na', 'not applicable'], true)) {
            throw ValidationException::withMessages([
                'clinical_impression' => 'Enter the doctor’s clinical impression; placeholder values such as “none” are not allowed.',
            ]);
        }

        $data['consultation_performed'] = $request->boolean('consultation_performed');
        $data['physical_examination_performed'] = $request->boolean('physical_examination_performed');

        return $data;
    }

    private function assertIssuable(MedicalCertificate $certificate)
    {
        $errors = [];
        if (! trim((string) $certificate->clinical_impression)
            || in_array(strtolower(trim($certificate->clinical_impression)), ['none', 'n/a', 'na', 'not applicable'], true)) {
            $errors['clinical_impression'] = 'A proper clinical impression is required before issuance.';
        }
        if (in_array($certificate->fitness_status, ['fit_with_restrictions', 'other'], true) && ! trim((string) $certificate->fitness_details)) {
            $errors['fitness_details'] = 'Fitness details are required for this assessment.';
        }
        if ($certificate->fitness_status === 'physically_unfit' && ! trim((string) $certificate->remarks)) {
            $errors['remarks'] = 'Remarks are required when the patient is physically unfit.';
        }
        if ($certificate->purpose === 'other' && ! trim((string) $certificate->purpose_other)) {
            $errors['purpose_other'] = 'Specify the certificate purpose before issuance.';
        }
        if ($errors) {
            throw ValidationException::withMessages($errors);
        }
    }

    private function documentData(MedicalCertificate $certificate)
    {
        $certificate->loadMissing(['patient', 'doctor.doctorProfile', 'consultation']);
        $logoPath = public_path('img/msu-iit-logo-print.jpg');
        $logoData = is_readable($logoPath)
            ? 'data:image/jpeg;base64,'.base64_encode(file_get_contents($logoPath))
            : null;
        $signatureData = null;
        $profile = optional($certificate->doctor)->doctorProfile;

        if ($profile && $profile->signature_status === 'verified'
            && (int) $profile->signature_version === (int) $certificate->signature_version
            && $profile->signature_path
            && Storage::disk('local')->exists($profile->signature_path)) {
            $extension = strtolower(pathinfo($profile->signature_path, PATHINFO_EXTENSION) ?: 'png');
            $signatureData = 'data:image/'.$extension.';base64,'.base64_encode(Storage::disk('local')->get($profile->signature_path));
        }

        return compact('certificate', 'logoData', 'signatureData');
    }

    private function doctorOwns(Request $request, MedicalCertificate $certificate)
    {
        abort_unless(
            optional($request->user()->role)->name === 'Doctor'
            && (int) $certificate->issued_by_doctor_id === (int) $request->user()->id,
            403
        );
    }

    private function canView(Request $request, MedicalCertificate $certificate)
    {
        $role = optional($request->user()->role)->name;
        $account = $request->user()->patientAccount;
        $patientAccountId = PatientAccount::where('patient_id', $certificate->patient_id)->value('id');

        abort_unless(
            in_array($role, ['Administrator', 'Doctor', 'Nurse', 'Staff'], true)
            || ($account && $patientAccountId && $account->accessibleAccountIds()->contains($patientAccountId)),
            403
        );
    }

    private function canViewDraft(Request $request, MedicalCertificate $certificate)
    {
        abort_unless(
            $certificate->status === 'issued'
            || (int) $certificate->issued_by_doctor_id === (int) $request->user()->id,
            403
        );
    }

    private function number()
    {
        return 'MC-'.now()->format('Ymd').'-'.str_pad((string) (MedicalCertificate::whereDate('created_at', today())->count() + 1), 4, '0', STR_PAD_LEFT);
    }
}
