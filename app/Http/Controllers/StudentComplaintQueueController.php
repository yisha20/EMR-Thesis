<?php

namespace App\Http\Controllers;

use App\ComplaintStatusLog;
use App\ClinicNotification;
use App\Consultation;
use App\CounterService;
use App\Helpers\ActivityLogger;
use App\HealthExaminationRecord;
use App\MedicalRecord;
use App\Patient;
use App\Prescription;
use App\Services\PrescriptionPdfService;
use App\Student;
use App\StudentComplaint;
use App\User;
use Illuminate\Http\Request;
use Illuminate\Http\File;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;
use Illuminate\Validation\ValidationException;

class StudentComplaintQueueController extends Controller
{
    public function index(Request $request)
    {
        $complaints = StudentComplaint::with(['student.user', 'reviewer'])
            ->when($request->user()->role->name === 'Doctor', function ($query) {
                $query->whereHas('consultation');
            })
            ->when($request->filled('student_id'), function ($query) use ($request) {
                $query->where('student_id_number', 'LIKE', '%' . $request->student_id . '%');
            })
            ->when($request->filled('status'), function ($query) use ($request) {
                $query->where('status', $request->status);
            })
            ->latest('submitted_at')
            ->paginate(20)
            ->appends($request->query());

        $searchedStudent = null;
        if ($request->filled('student_id')) {
            $searchedStudent = Student::with(['user', 'complaints' => function ($query) {
                $query->latest('submitted_at');
            }])->where('student_id_number', $request->student_id)->first();
        }

        return view('student.staff.queue', compact('complaints', 'searchedStudent'));
    }

    public function show(StudentComplaint $complaint)
    {
        $complaint->load([
            'student.user', 'reviewer', 'statusLogs.changedBy', 'patient.medicalRecords',
            'medicalRecord', 'counterService.handler', 'consultation.forwarder', 'consultation.doctor', 'consultation.prescription.patient', 'consultation.prescription.doctor',
        ]);
        $matchingPatients = Patient::where('id_number', $complaint->student_id_number)->get();

        return view('student.staff.show', compact('complaint', 'matchingPatients'));
    }

    public function updateStatus(Request $request, StudentComplaint $complaint)
    {
        $data = $request->validate([
            'status' => 'required|in:Reviewed',
            'notes' => 'nullable|string|max:1000',
        ]);

        abort_unless($complaint->status === 'Pending', 422, 'Only pending complaints can be reviewed.');

        DB::transaction(function () use ($complaint, $data, $request) {
            $complaint = StudentComplaint::whereKey($complaint->id)->lockForUpdate()->firstOrFail();
            $fromStatus = $complaint->status;
            $complaint->status = $data['status'];
            $complaint->reviewed_by = $complaint->reviewed_by ?: $request->user()->id;

            $this->reviewComplaint($complaint, $request);

            $complaint->save();

            ComplaintStatusLog::create([
                'student_complaint_id' => $complaint->id,
                'changed_by' => $request->user()->id,
                'from_status' => $fromStatus,
                'to_status' => $data['status'],
                'notes' => $data['notes'] ?? null,
            ]);

            ActivityLogger::log('reviewed student complaint (' . $complaint->student_name . ')');
        });

        return redirect()->back()->with('success', 'Complaint status updated.');
    }

    public function createPatientRecord(Request $request, StudentComplaint $complaint)
    {
        $patient = DB::transaction(function () use ($request, $complaint) {
            $complaint = StudentComplaint::whereKey($complaint->id)->lockForUpdate()->firstOrFail();

            return $this->syncPatient($complaint, $request, false);
        });

        return redirect()->route('student-complaints.show', $complaint)
            ->with('success', 'Patient record created or updated and linked to the complaint.');
    }

    public function resolveCounter(Request $request, StudentComplaint $complaint)
    {
        $data = $request->validate([
            'remedy_given' => 'required|string|max:5000',
            'quantity' => 'nullable|string|max:100',
            'notes' => 'nullable|string|max:5000',
            'outcome' => 'required|in:Resolved,Advised to return if symptoms persist,Referred for consultation',
        ]);

        abort_unless($complaint->status === 'Reviewed', 422, 'The complaint must be reviewed before counter resolution.');

        DB::transaction(function () use ($request, $complaint, $data) {
            $complaint = StudentComplaint::whereKey($complaint->id)->lockForUpdate()->firstOrFail();
            abort_unless($complaint->status === 'Reviewed', 422, 'The complaint has already been processed.');
            $patient = $this->syncPatient($complaint, $request, true);
            $handledAt = now();

            $counterService = CounterService::create([
                'student_complaint_id' => $complaint->id,
                'patient_id' => $patient->id,
                'remedy_given' => $data['remedy_given'],
                'quantity' => $data['quantity'] ?? null,
                'notes' => $data['notes'] ?? null,
                'handled_by' => $request->user()->id,
                'outcome' => $data['outcome'],
                'handled_at' => $handledAt,
            ]);

            $record = MedicalRecord::updateOrCreate(
                ['student_complaint_id' => $complaint->id],
                [
                    'patient_id' => $patient->id,
                    'counter_service_id' => $counterService->id,
                    'consultation_id' => null,
                    'record_type' => 'Counter Remedy',
                    'source' => 'Student Intake / Counter Service',
                    'description' => $data['remedy_given'],
                    'outcome' => $data['outcome'],
                    'consultation_status' => 'Counter Resolved',
                    'date_of_consultation' => $handledAt->toDateString(),
                    'time_of_consultation' => $handledAt->format('H:i:s'),
                    'chief_complaint' => $complaint->chief_complaint,
                    'symptoms_description' => $complaint->symptoms_description,
                    'history_of_present_illness' => $complaint->symptoms_description,
                    'urgency_level' => $complaint->urgency_level,
                    'recommendation' => $data['remedy_given'],
                    'findings' => $data['notes'] ?? null,
                    'submitted_at' => $complaint->submitted_at,
                    'reviewed_by' => $complaint->reviewed_by,
                    'reviewed_at' => $complaint->reviewed_at,
                    'attending_staff_id' => $request->user()->id,
                    'created_by' => $request->user()->id,
                    'nurse_assigned' => $request->user()->fullName(),
                ]
            );

            $fromStatus = $complaint->status;
            $complaint->update([
                'patient_id' => $patient->id,
                'medical_record_id' => $record->id,
                'status' => 'Counter Resolved',
                'completed_at' => $handledAt,
            ]);
            $this->logStatus($complaint, $request, $fromStatus, 'Counter Resolved', $data['notes'] ?? 'Resolved at clinic counter.');
            ActivityLogger::log('completed counter remedy (' . $complaint->student_name . ')', $data['remedy_given']);
        });

        return redirect()->route('student-complaints.show', $complaint)->with('success', 'Counter remedy saved to the patient medical record.');
    }

    public function forwardConsultation(Request $request, StudentComplaint $complaint)
    {
        $data = $request->validate([
            'service_needed' => 'required|in:Checkup,Medical Consultation,Dental Consultation,Physical Examination,Laboratory Request,Other service',
            'priority' => 'required|in:Low,Moderate,High',
            'nurse_notes' => 'nullable|string|max:5000',
        ]);

        abort_unless($complaint->status === 'Reviewed', 422, 'The complaint must be reviewed before forwarding.');

        DB::transaction(function () use ($request, $complaint, $data) {
            $complaint = StudentComplaint::whereKey($complaint->id)->lockForUpdate()->firstOrFail();
            abort_unless($complaint->status === 'Reviewed', 422, 'The complaint has already been processed.');
            $patient = $this->syncPatient($complaint, $request, true);
            $forwardedAt = now();

            $consultation = Consultation::create([
                'student_complaint_id' => $complaint->id,
                'patient_id' => $patient->id,
                'service_needed' => $data['service_needed'],
                'priority' => $data['priority'],
                'nurse_notes' => $data['nurse_notes'] ?? null,
                'forwarded_by' => $request->user()->id,
                'forwarded_at' => $forwardedAt,
                'status' => 'Pending Consultation',
            ]);

            $record = MedicalRecord::updateOrCreate(
                ['student_complaint_id' => $complaint->id],
                [
                    'patient_id' => $patient->id,
                    'counter_service_id' => null,
                    'consultation_id' => $consultation->id,
                    'record_type' => 'Consultation',
                    'source' => 'Doctor Consultation',
                    'description' => $data['nurse_notes'] ?? $complaint->symptoms_description,
                    'outcome' => 'Pending Consultation',
                    'consultation_status' => 'Pending Consultation',
                    'performed_service' => $data['service_needed'],
                    'date_of_consultation' => $forwardedAt->toDateString(),
                    'time_of_consultation' => $forwardedAt->format('H:i:s'),
                    'chief_complaint' => $complaint->chief_complaint,
                    'symptoms_description' => $complaint->symptoms_description,
                    'history_of_present_illness' => $complaint->symptoms_description,
                    'urgency_level' => $data['priority'],
                    'submitted_at' => $complaint->submitted_at,
                    'reviewed_by' => $complaint->reviewed_by,
                    'reviewed_at' => $complaint->reviewed_at,
                    'attending_staff_id' => $request->user()->id,
                    'created_by' => $request->user()->id,
                    'nurse_assigned' => $request->user()->fullName(),
                ]
            );

            $fromStatus = $complaint->status;
            $complaint->update([
                'patient_id' => $patient->id,
                'medical_record_id' => $record->id,
                'status' => 'Forwarded',
            ]);
            $this->logStatus($complaint, $request, $fromStatus, 'Forwarded', $data['nurse_notes'] ?? 'Forwarded to doctor consultation queue.');
            ActivityLogger::log('forwarded complaint to consultation (' . $complaint->student_name . ')', $data['service_needed']);
        });

        return redirect()->route('student-complaints.show', $complaint)->with('success', 'Complaint forwarded to the doctor consultation queue.');
    }

    public function startConsultation(Request $request, StudentComplaint $complaint)
    {
        DB::transaction(function () use ($request, $complaint) {
            $consultation = Consultation::where('student_complaint_id', $complaint->id)->lockForUpdate()->firstOrFail();
            abort_unless(in_array($consultation->status, ['Pending Consultation', 'Called'], true), 422, 'This consultation cannot be started.');
            $startedAt = now();
            $fromStatus = $complaint->status;
            $consultation->update(['status' => 'In Consultation', 'started_at' => $startedAt, 'doctor_id' => $request->user()->id]);
            $complaint->update(['status' => 'In Consultation', 'consultation_started_at' => $startedAt]);
            $complaint->medicalRecord()->update([
                'consultation_status' => 'In Consultation',
                'outcome' => 'In Consultation',
                'attending_staff_id' => $request->user()->id,
                'attending_physician' => $request->user()->fullName(),
            ]);
            $this->logStatus($complaint, $request, $fromStatus, 'In Consultation', 'Doctor started consultation.');
            ActivityLogger::log('started consultation (' . $complaint->student_name . ')');
        });

        return redirect()->back()->with('success', 'Consultation started.');
    }

    public function completeConsultation(Request $request, StudentComplaint $complaint, PrescriptionPdfService $pdfService)
    {
        $data = $request->validate([
            'diagnosis' => 'required|string|max:5000',
            'treatment' => 'required|string|max:5000',
            'prescription' => 'nullable|string|max:5000',
            'prescription_type' => 'nullable|in:Medication,Laboratory Request,Medical Certificate,Other',
            'medications' => 'nullable|array|max:20',
            'medications.*.medication' => 'nullable|string|max:255',
            'medications.*.dosage' => 'nullable|string|max:255',
            'medications.*.frequency' => 'nullable|string|max:255',
            'medications.*.duration' => 'nullable|string|max:255',
            'medications.*.instruction' => 'nullable|string|max:1000',
            'doctor_notes' => 'nullable|string|max:5000',
            'additional_instructions' => 'nullable|string|max:5000',
            'follow_up_date' => 'nullable|date|after_or_equal:today',
            'attachment' => 'nullable|file|max:5120|mimes:jpg,jpeg,png,pdf,doc,docx',
        ]);

        $medications = collect($data['medications'] ?? [])->map(function ($medication) {
            return collect(['medication', 'dosage', 'frequency', 'duration', 'instruction'])
                ->mapWithKeys(function ($field) use ($medication) { return [$field => trim((string) ($medication[$field] ?? ''))]; })
                ->all();
        })->filter(function ($medication) {
            return collect($medication)->filter()->isNotEmpty();
        })->values()->all();

        if (($data['prescription_type'] ?? null) === 'Medication' && empty($medications)) {
            throw ValidationException::withMessages(['medications' => 'Add at least one medication before completing the consultation.']);
        }

        foreach ($medications as $index => $medication) {
            if ($medication['medication'] === '') {
                throw ValidationException::withMessages(["medications.$index.medication" => 'Medication name is required.']);
            }
        }

        $attachment = null;
        if ($request->hasFile('attachment') && $request->file('attachment')->isValid()) {
            $path = Storage::disk('public')->putFile('consultations', new File($request->file('attachment')), 'public');
            $attachment = Storage::url($path);
        }

        $prescription = DB::transaction(function () use ($request, $complaint, $data, $attachment, $medications, $pdfService) {
            $consultation = Consultation::where('student_complaint_id', $complaint->id)->lockForUpdate()->firstOrFail();
            abort_unless($consultation->status === 'In Consultation', 422, 'Start the consultation before completing it.');
            $completedAt = now();
            $consultation->update([
                'status' => 'Completed',
                'completed_at' => $completedAt,
                'doctor_id' => $request->user()->id,
                'diagnosis' => $data['diagnosis'],
                'treatment' => $data['treatment'],
                'prescription' => $this->prescriptionSummary($data['prescription_type'] ?? null, $medications, $data['prescription'] ?? null),
                'doctor_notes' => $data['doctor_notes'] ?? null,
                'follow_up_date' => $data['follow_up_date'] ?? null,
                'attachment' => $attachment ?: $consultation->attachment,
            ]);
            $complaint->update([
                'status' => 'Completed',
                'completed_at' => $completedAt,
                'diagnosis' => $data['diagnosis'],
                'treatment' => $data['treatment'],
                'prescription' => $this->prescriptionSummary($data['prescription_type'] ?? null, $medications, $data['prescription'] ?? null),
            ]);
            $complaint->medicalRecord()->update([
                'consultation_status' => 'Completed',
                'outcome' => 'Completed',
                'diagnosis' => $data['diagnosis'],
                'recommendation' => $data['additional_instructions'] ?? $data['treatment'],
                'medication_taken' => $this->prescriptionSummary($data['prescription_type'] ?? null, $medications, $data['prescription'] ?? null),
                'findings' => $data['doctor_notes'] ?? null,
                'file' => $attachment ?: optional($complaint->medicalRecord)->file,
                'attending_staff_id' => $request->user()->id,
                'attending_physician' => $request->user()->fullName(),
            ]);
            $this->logStatus($complaint, $request, 'In Consultation', 'Completed', $data['doctor_notes'] ?? 'Doctor completed consultation.');
            ActivityLogger::log('completed consultation for ' . $complaint->student_name, $data['diagnosis']);

            User::where('status', 'Active')
                ->whereHas('role', function ($query) { $query->whereIn('name', ['Nurse', 'Staff']); })
                ->get()
                ->each(function ($recipient) use ($consultation, $complaint) {
                    ClinicNotification::create([
                        'user_id' => $recipient->id,
                        'role_target' => $recipient->role->name,
                        'title' => 'Consultation Completed',
                        'message' => $complaint->student_name . "'s consultation has been completed. You may now call the next student.",
                        'type' => 'consultation_completed',
                        'related_consultation_id' => $consultation->id,
                        'related_patient_id' => $consultation->patient_id,
                    ]);
                });

            if (empty($data['prescription_type'])) {
                return null;
            }

            $prescription = Prescription::create([
                'consultation_id' => $consultation->id,
                'patient_id' => $consultation->patient_id,
                'doctor_id' => $request->user()->id,
                'prescription_number' => 'RX-' . $completedAt->format('Y') . '-' . str_pad($consultation->id, 6, '0', STR_PAD_LEFT),
                'prescription_type' => $data['prescription_type'],
                'medications' => $medications ?: null,
                'additional_instructions' => $data['additional_instructions'] ?? null,
                'follow_up_date' => $data['follow_up_date'] ?? null,
            ]);

            $pdfService->generate($prescription);
            MedicalRecord::create([
                'patient_id' => $consultation->patient_id,
                'consultation_id' => $consultation->id,
                'prescription_id' => $prescription->id,
                'record_type' => 'Prescription',
                'source' => 'Doctor Consultation',
                'description' => $prescription->summary,
                'outcome' => 'Issued',
                'consultation_status' => 'Completed',
                'date_of_consultation' => $completedAt->toDateString(),
                'time_of_consultation' => $completedAt->format('H:i:s'),
                'chief_complaint' => $complaint->chief_complaint,
                'diagnosis' => $data['diagnosis'],
                'medication_taken' => $prescription->summary,
                'recommendation' => $data['additional_instructions'] ?? null,
                'attending_staff_id' => $request->user()->id,
                'attending_physician' => $request->user()->fullName(),
                'created_by' => $request->user()->id,
            ]);
            ActivityLogger::log('generated prescription for ' . $complaint->student_name, $prescription->prescription_number);

            return $prescription;
        });

        if ($prescription && $request->boolean('print_after')) {
            return redirect()->route('student-complaints.show', $complaint)
                ->with('success', 'Consultation completed and prescription generated.')
                ->with('open_prescription_id', $prescription->id)
                ->with('print_prescription_id', $prescription->id);
        }

        if ($prescription) {
            return redirect()->route('student-complaints.show', $complaint)
                ->with('success', 'Consultation completed and prescription generated.')
                ->with('open_prescription_id', $prescription->id);
        }

        return redirect()->route('student-complaints.show', $complaint)->with('success', 'Consultation completed and medical record updated.');
    }

    private function prescriptionSummary($type, array $medications, $legacyPrescription = null)
    {
        if (!empty($medications)) {
            return collect($medications)->map(function ($medication) {
                return trim($medication['medication'] . ' ' . $medication['dosage']);
            })->implode(', ');
        }

        return $legacyPrescription ?: $type;
    }

    private function logStatus(StudentComplaint $complaint, Request $request, $from, $to, $notes = null)
    {
        ComplaintStatusLog::create([
            'student_complaint_id' => $complaint->id,
            'changed_by' => $request->user()->id,
            'from_status' => $from,
            'to_status' => $to,
            'notes' => $notes,
        ]);
    }

    private function reviewComplaint(StudentComplaint $complaint, Request $request)
    {
        $this->syncPatient($complaint, $request, true);
    }

    private function syncPatient(StudentComplaint $complaint, Request $request, $markReviewed)
    {
        $student = $complaint->student()->with('user')->firstOrFail();
        $user = $student->user;
        $processedAt = now();
        $patientData = [
            'id_number' => $student->student_id_number,
            'first_name' => $student->first_name ?: $user->first_name,
            'middle_name' => $student->middle_name ?: $user->middle_name,
            'last_name' => $student->last_name ?: $user->last_name,
            'email' => $student->email ?: $user->email,
            'gender' => $student->gender ?: $user->gender,
            'birthdate' => $student->birth_date ?: $user->birthdate,
            'age' => $student->age !== null ? $student->age : $user->age,
            'civil_status' => $student->civil_status ?: $user->civil_status,
            'home_address' => $student->home_address ?: $user->home_address,
            'present_address' => $student->present_address ?: $user->present_address,
            'college_department' => $student->college_department,
            'phone_number' => $student->contact_number ?: $user->phone_number,
            'type' => 'Student',
        ];

        $patient = Patient::withTrashed()
            ->where('id_number', $student->student_id_number)
            ->lockForUpdate()
            ->first();

        if ($patient) {
            if ($patient->trashed()) {
                $patient->restore();
            }

            $updates = [
                'status' => 'Active',
                'updated_by' => $request->user()->id,
            ];

            foreach ($patientData as $field => $value) {
                if (($patient->{$field} === null || $patient->{$field} === '') && $value !== null && $value !== '') {
                    $updates[$field] = $value;
                }
            }

            if ($markReviewed) {
                $updates['last_reviewed_at'] = $processedAt;
            }

            $patient->update($updates);
            ActivityLogger::log('linked complaint to existing patient (' . $complaint->student_name . ')');
        } else {
            $patient = Patient::create(array_merge($patientData, [
                'status' => 'Active',
                'added_by' => $request->user()->id,
                'updated_by' => $request->user()->id,
                'date_registered' => $processedAt,
                'last_reviewed_at' => $markReviewed ? $processedAt : null,
            ]));
            ActivityLogger::log('Created patient record from student intake: ' . $student->full_name);
        }

        $healthRecord = HealthExaminationRecord::firstOrCreate(
            ['patient_id' => $patient->id],
            [
                'past_medical_history' => [
                    'pastmedical_history' => [],
                    'last_menstrual_period' => '',
                    'menstrual_pattern' => '',
                ],
                'family_history' => ['family_history' => []],
                'social_history' => [
                    'is_smoking' => '',
                    'packs_smoked' => '',
                    'is_drinking_beer' => '',
                    'drinking_frequency' => '',
                    'is_taking_medication' => '',
                    'medications' => [],
                    'allergies' => [],
                    'exercise' => '',
                    'diet' => '',
                ],
                'phyiscal_examination' => [],
                'vital_signs' => [
                    'temperature' => '',
                    'pulse_rate' => '',
                    'respiratory_rate' => '',
                    'blood_pressure' => '',
                    'weight' => '',
                ],
                'assessment' => [
                    'physically_fit' => '',
                    'physically_fit_description' => '',
                    'date_examined' => '',
                    'by' => '',
                    'license_no' => '',
                ],
                'nursing_interventions' => ['nursing_interventions' => []],
                'added_by' => $request->user()->id,
            ]
        );
        $this->normalizeHealthRecordStructure($healthRecord);

        $complaint->patient_id = $patient->id;
        if ($markReviewed) {
            $complaint->reviewed_at = $processedAt;
        }
        $complaint->save();

        return $patient;
    }

    private function normalizeHealthRecordStructure(HealthExaminationRecord $record)
    {
        $pastMedicalHistory = array_merge([
            'pastmedical_history' => [],
            'last_menstrual_period' => '',
            'menstrual_pattern' => '',
        ], $this->normalizeStructuredValue($record->past_medical_history));
        $familyHistory = array_merge([
            'family_history' => [],
        ], $this->normalizeStructuredValue($record->family_history));
        $socialHistory = array_merge([
            'is_smoking' => '',
            'packs_smoked' => '',
            'is_drinking_beer' => '',
            'drinking_frequency' => '',
            'is_taking_medication' => '',
            'medications' => [],
            'allergies' => [],
            'exercise' => '',
            'diet' => '',
        ], $this->normalizeStructuredValue($record->social_history));
        $nursingInterventions = $this->normalizeStructuredValue($record->nursing_interventions);

        $pastMedicalHistory['pastmedical_history'] = $this->normalizeListValue($pastMedicalHistory['pastmedical_history'] ?? []);
        $familyHistory['family_history'] = $this->normalizeListValue($familyHistory['family_history'] ?? []);
        $socialHistory['medications'] = $this->normalizeListValue($socialHistory['medications'] ?? []);
        $socialHistory['allergies'] = $this->normalizeListValue($socialHistory['allergies'] ?? []);
        $nursingInterventions['nursing_interventions'] = $this->normalizeListValue(
            $nursingInterventions['nursing_interventions'] ?? []
        );

        $record->update([
            'past_medical_history' => $pastMedicalHistory,
            'family_history' => $familyHistory,
            'social_history' => $socialHistory,
            'phyiscal_examination' => $this->normalizeStructuredValue($record->phyiscal_examination),
            'vital_signs' => array_merge([
                'temperature' => '',
                'pulse_rate' => '',
                'respiratory_rate' => '',
                'blood_pressure' => '',
                'weight' => '',
            ], $this->normalizeStructuredValue($record->vital_signs)),
            'assessment' => array_merge([
                'physically_fit' => '',
                'physically_fit_description' => '',
                'date_examined' => '',
                'by' => '',
                'license_no' => '',
            ], $this->normalizeStructuredValue($record->assessment)),
            'nursing_interventions' => $nursingInterventions,
        ]);
    }

    private function normalizeStructuredValue($value)
    {
        if ($value instanceof \Illuminate\Support\Collection) {
            return $value->toArray();
        }

        if (is_array($value)) {
            return $value;
        }

        if ($value === null || $value === '') {
            return [];
        }

        if (is_string($value)) {
            $decoded = json_decode($value, true);
            if (json_last_error() === JSON_ERROR_NONE) {
                return is_array($decoded) ? $decoded : [$decoded];
            }

            return strpos($value, ',') !== false
                ? array_values(array_filter(array_map('trim', explode(',', $value)), 'strlen'))
                : [$value];
        }

        return [$value];
    }

    private function normalizeListValue($value)
    {
        return array_values($this->normalizeStructuredValue($value));
    }

    public function linkRecord(Request $request, StudentComplaint $complaint)
    {
        $data = $request->validate([
            'patient_id' => 'required|exists:patients,id',
            'medical_record_id' => 'nullable|exists:medical_records,id',
        ]);

        if (!empty($data['medical_record_id'])) {
            $belongsToPatient = MedicalRecord::where('id', $data['medical_record_id'])
                ->where('patient_id', $data['patient_id'])
                ->exists();
            abort_unless($belongsToPatient, 422, 'The selected medical record does not belong to this patient.');
        }

        $complaint->update([
            'patient_id' => $data['patient_id'],
            'medical_record_id' => $data['medical_record_id'] ?? null,
        ]);

        return redirect()->back()->with('success', 'Patient or medical record linked successfully.');
    }

    public function updateClinicalNotes(Request $request, StudentComplaint $complaint)
    {
        $data = $request->validate([
            'diagnosis' => 'required|string|max:5000',
            'treatment' => 'nullable|string|max:5000',
            'prescription' => 'nullable|string|max:5000',
        ]);

        $complaint->update($data);
        if ($complaint->medicalRecord) {
            $complaint->medicalRecord->update([
                'diagnosis' => $data['diagnosis'],
                'recommendation' => $data['treatment'] ?? null,
                'medication_taken' => $data['prescription'] ?? null,
                'attending_physician' => $request->user()->role->name === 'Doctor'
                    ? $request->user()->fullName()
                    : $complaint->medicalRecord->attending_physician,
            ]);
        }
        ActivityLogger::log('added diagnosis/treatment (' . $complaint->student_name . ')');

        return redirect()->back()->with('success', 'Clinical notes saved.');
    }
}
