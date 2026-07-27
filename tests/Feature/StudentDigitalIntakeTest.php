<?php

namespace Tests\Feature;

use App\Role;
use App\MedicalRecord;
use App\Models\ActivityLog;
use App\Patient;
use App\HealthExaminationRecord;
use App\Student;
use App\StudentComplaint;
use App\User;
use Illuminate\Foundation\Testing\DatabaseTransactions;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Storage;
use Tests\TestCase;

class StudentDigitalIntakeTest extends TestCase
{
    use DatabaseTransactions;

    public function test_student_registration_creates_linked_complete_profile_and_redirects_to_login()
    {
        Role::firstOrCreate(['name' => 'Student']);

        $data = [
            'student_id_number' => '2026-1000',
            'first_name' => 'New',
            'middle_name' => 'IIT',
            'last_name' => 'Student',
            'email' => 'new-student@example.test',
            'password' => 'password123',
            'password_confirmation' => 'password123',
            'college_department' => 'College of Science and Mathematics',
            'contact_number' => '09171234567',
            'gender' => 'Female',
            'birth_date' => '2006-02-14',
            'age' => 20,
            'civil_status' => 'Single',
            'home_address' => 'Lanao del Norte',
            'present_address' => 'Iligan City',
        ];

        $response = $this->post(route('student.register.store'), $data);

        $response->assertRedirect(route('login'));
        $response->assertSessionHas('success');
        $this->assertGuest();

        $user = User::where('email', $data['email'])->firstOrFail();
        $student = Student::where('student_id_number', $data['student_id_number'])->firstOrFail();

        $this->assertSame($user->id, $student->user_id);
        $this->assertSame('New IIT Student', $user->name);
        $this->assertSame('Active', $user->status);
        $this->assertTrue($user->isStudent());
        $this->assertTrue(Hash::check('password123', $user->password));

        $this->assertDatabaseHas('students', [
            'user_id' => $user->id,
            'student_id_number' => '2026-1000',
            'first_name' => 'New',
            'middle_name' => 'IIT',
            'last_name' => 'Student',
            'email' => 'new-student@example.test',
            'college_department' => 'College of Science and Mathematics',
            'contact_number' => '09171234567',
            'gender' => 'Female',
            'birth_date' => '2006-02-14',
            'age' => 20,
            'civil_status' => 'Single',
            'home_address' => 'Lanao del Norte',
            'present_address' => 'Iligan City',
        ]);
        $this->assertDatabaseHas('activity_logs', [
            'user_id' => $user->id,
            'action' => 'Student registered account: New IIT Student',
        ]);
    }

    public function test_registered_student_can_login_with_email_and_password()
    {
        Role::firstOrCreate(['name' => 'Student']);
        $this->post(route('student.register.store'), $this->registrationData('2026-1020', 'login-student@example.test'));

        $this->post(route('login'), [
            'email' => 'login-student@example.test',
            'password' => 'password123',
        ])->assertRedirect(route('patient.assessment.edit'));

        $this->assertAuthenticatedAs(User::where('email', 'login-student@example.test')->firstOrFail());
    }

    public function test_registration_rejects_duplicate_email_and_iit_id()
    {
        Role::firstOrCreate(['name' => 'Student']);
        $this->post(route('student.register.store'), $this->registrationData('2026-1021', 'duplicate@example.test'));

        $this->post(route('student.register.store'), $this->registrationData('2026-1022', 'duplicate@example.test'))
            ->assertSessionHasErrors('email');

        $this->post(route('student.register.store'), $this->registrationData('2026-1021', 'different@example.test'))
            ->assertSessionHasErrors('student_id_number');

        $this->assertSame(1, User::where('email', 'duplicate@example.test')->count());
        $this->assertSame(1, Student::where('student_id_number', '2026-1021')->count());
    }

    public function test_registration_rejects_password_mismatch()
    {
        Role::firstOrCreate(['name' => 'Student']);
        $data = $this->registrationData('2026-1023', 'mismatch@example.test');
        $data['password_confirmation'] = 'different-password';

        $this->post(route('student.register.store'), $data)
            ->assertSessionHasErrors('password');

        $this->assertDatabaseMissing('users', ['email' => 'mismatch@example.test']);
        $this->assertDatabaseMissing('students', ['student_id_number' => '2026-1023']);
    }

    public function test_inactive_student_account_cannot_login()
    {
        [$user] = $this->createStudentAccount('2026-1024');
        $user->update(['status' => 'Inactive']);

        $this->post(route('login'), [
            'email' => $user->email,
            'password' => 'password123',
        ])->assertSessionHasErrors('email');

        $this->assertGuest();
    }

    public function test_student_can_submit_a_complaint_to_the_staff_queue()
    {
        [$user, $student] = $this->createStudentAccount('2026-1005');

        $this->actingAs($user)
            ->post(route('student.complaints.store'), [
                'complaint_category' => 'General Consultation',
                'chief_complaint' => 'Fever',
                'symptoms_description' => 'Fever and body pain since last night.',
                'urgency_level' => 'High',
            ])
            ->assertRedirect(route('student.complaints.index'));

        $this->assertDatabaseHas('student_complaints', [
            'student_id' => $student->id,
            'complaint_category' => 'General symptoms',
            'chief_complaint' => 'Fever',
            'urgency_level' => 'Unassigned',
            'triage_priority' => 'unassigned',
            'status' => 'Pending',
        ]);
    }

    public function test_student_can_access_own_dashboard_but_not_staff_pages()
    {
        [$user] = $this->createStudentAccount('2026-1001');

        $this->actingAs($user)
            ->get(route('student.dashboard'))
            ->assertStatus(200);

        $this->actingAs($user)
            ->get(route('dashboard'))
            ->assertStatus(403);

        $this->actingAs($user)
            ->get(route('student-complaints.index'))
            ->assertStatus(403);

        foreach ([
            route('patients.index'),
            route('users.index'),
            route('services.index'),
        ] as $restrictedUrl) {
            $this->actingAs($user)->get($restrictedUrl)->assertStatus(403);
        }
    }

    public function test_student_cannot_view_another_students_complaint()
    {
        [$firstUser] = $this->createStudentAccount('2026-1002');
        [, $secondStudent] = $this->createStudentAccount('2026-1003');
        $complaint = $this->createComplaint($secondStudent);

        $this->actingAs($firstUser)
            ->get(route('student.complaints.show', $complaint))
            ->assertStatus(403);
    }

    public function test_nurse_can_review_pending_complaint()
    {
        [, $student] = $this->createStudentAccount('2026-1004');
        $complaint = $this->createComplaint($student);
        $nurse = $this->createStaffUser('Nurse', 'nurse-intake@example.test');

        $this->actingAs($nurse)
            ->patch(route('student-complaints.status', $complaint), ['status' => 'Reviewed'])
            ->assertRedirect();

        $this->assertDatabaseHas('student_complaints', [
            'id' => $complaint->id,
            'status' => 'Reviewed',
            'reviewed_by' => $nurse->id,
        ]);

        $this->assertDatabaseHas('complaint_status_logs', [
            'student_complaint_id' => $complaint->id,
            'from_status' => 'Pending',
            'to_status' => 'Reviewed',
        ]);

        $patient = Patient::where('id_number', '2026-1004')->firstOrFail();
        $this->assertSame('Active', $patient->status);
        $this->assertSame($student->user->email, $patient->email);
        $this->assertNotNull($patient->date_registered);

        $complaint->refresh();
        $this->assertSame($patient->id, $complaint->patient_id);
        $this->assertNull($complaint->medical_record_id);
        $this->assertDatabaseMissing('medical_records', ['student_complaint_id' => $complaint->id]);

        $this->assertTrue(ActivityLog::where('action', 'LIKE', '%reviewed student complaint%')->exists());
        $this->assertTrue(ActivityLog::where('action', 'LIKE', '%Created patient record from student intake:%')->exists());
    }

    public function test_create_patient_record_button_copies_complete_student_profile_and_links_complaint()
    {
        [, $student] = $this->createStudentAccount('2026-1030');
        $student->update([
            'first_name' => 'Complete',
            'middle_name' => 'Profile',
            'last_name' => 'Student',
            'email' => 'complete-profile@example.test',
            'gender' => 'Female',
            'birth_date' => '2005-03-21',
            'age' => 21,
            'civil_status' => 'Single',
            'home_address' => 'Home Address',
            'present_address' => 'Present Address',
            'college_department' => 'College of Computer Studies',
            'contact_number' => '09991234567',
        ]);
        $complaint = $this->createComplaint($student->fresh());
        $nurse = $this->createStaffUser('Nurse', 'nurse-create-patient@example.test');

        $this->actingAs($nurse)
            ->post(route('student-complaints.create-patient', $complaint))
            ->assertRedirect(route('student-complaints.show', $complaint));

        $patient = Patient::where('id_number', '2026-1030')->firstOrFail();
        $this->assertDatabaseHas('patients', [
            'id' => $patient->id,
            'id_number' => '2026-1030',
            'first_name' => 'Complete',
            'middle_name' => 'Profile',
            'last_name' => 'Student',
            'email' => 'complete-profile@example.test',
            'gender' => 'Female',
            'birthdate' => '2005-03-21',
            'age' => 21,
            'civil_status' => 'Single',
            'home_address' => 'Home Address',
            'present_address' => 'Present Address',
            'college_department' => 'College of Computer Studies',
            'phone_number' => '09991234567',
            'status' => 'Active',
        ]);

        $complaint->refresh();
        $this->assertSame($patient->id, $complaint->patient_id);
        $this->assertNull($complaint->medical_record_id);
        $this->assertDatabaseMissing('medical_records', ['student_complaint_id' => $complaint->id]);
        $this->assertDatabaseHas('activity_logs', [
            'user_id' => $nurse->id,
            'action' => $nurse->fullName() . ' Created patient record from student intake: Complete Profile Student',
        ]);

        $healthRecord = HealthExaminationRecord::where('patient_id', $patient->id)->firstOrFail();
        $this->assertSame('', $healthRecord->social_history['is_smoking']);
        $this->assertSame('', $healthRecord->social_history['packs_smoked']);
        $this->assertSame('', $healthRecord->social_history['is_drinking_beer']);
        $this->assertSame('', $healthRecord->social_history['drinking_frequency']);
        $this->assertSame('', $healthRecord->social_history['is_taking_medication']);
        $this->assertSame([], $healthRecord->social_history['medications']);
        $this->assertSame([], $healthRecord->nursing_interventions['nursing_interventions']);

        $this->actingAs($nurse)
            ->get(route('patients.show', $patient))
            ->assertStatus(200)
            ->assertSee('Complete Profile Student');

        $this->actingAs($nurse)
            ->get(route('patients.edit', $patient))
            ->assertStatus(200)
            ->assertSee('value="Complete"', false)
            ->assertSee('value="Profile"', false)
            ->assertSee('value="Student"', false);

        $this->actingAs($nurse)
            ->put(route('patients.update', $patient), [
                'id_number' => $patient->id_number,
                'first_name' => $patient->first_name,
                'middle_name' => $patient->middle_name,
                'last_name' => $patient->last_name,
                'packs_smoked' => 2,
                'is_smoking' => 'Yes',
                'patient_status' => 'Active',
            ])
            ->assertRedirect();

        $healthRecord->refresh();
        $this->assertSame('Yes', $healthRecord->social_history['is_smoking']);
        $this->assertSame('2', (string) $healthRecord->social_history['packs_smoked']);
        $this->assertSame([], $healthRecord->social_history['medications']);
    }

    public function test_create_patient_record_button_updates_missing_fields_without_duplicate()
    {
        [, $student] = $this->createStudentAccount('2026-1031');
        $complaint = $this->createComplaint($student);
        $nurse = $this->createStaffUser('Nurse', 'nurse-update-patient@example.test');
        $patient = Patient::create([
            'id_number' => $student->student_id_number,
            'first_name' => 'Existing',
            'last_name' => 'Patient',
            'status' => 'Inactive',
            'added_by' => $nurse->id,
        ]);

        $this->actingAs($nurse)
            ->post(route('student-complaints.create-patient', $complaint))
            ->assertRedirect(route('student-complaints.show', $complaint));

        $this->assertSame(1, Patient::where('id_number', '2026-1031')->count());
        $patient->refresh();
        $this->assertSame('Existing', $patient->first_name);
        $this->assertSame($student->email, $patient->email);
        $this->assertSame($student->college_department, $patient->college_department);
        $this->assertSame($student->contact_number, $patient->phone_number);
        $this->assertSame('Active', $patient->status);
        $this->assertSame($patient->id, $complaint->fresh()->patient_id);
    }

    public function test_patient_view_normalizes_legacy_string_history_values()
    {
        $nurse = $this->createStaffUser('Nurse', 'nurse-legacy-view@example.test');
        $patient = Patient::create([
            'id_number' => 'LEGACY-1001',
            'first_name' => 'Legacy',
            'last_name' => 'Patient',
            'status' => 'Active',
            'added_by' => $nurse->id,
        ]);
        HealthExaminationRecord::create([
            'patient_id' => $patient->id,
            'past_medical_history' => 'Allergies',
            'family_history' => null,
            'social_history' => 'Aspirin',
            'phyiscal_examination' => null,
            'vital_signs' => null,
            'assessment' => null,
            'nursing_interventions' => 'Observation',
            'added_by' => $nurse->id,
        ]);

        $this->actingAs($nurse)
            ->get(route('patients.show', $patient))
            ->assertStatus(200)
            ->assertSee('Legacy')
            ->assertSee('Patient');
    }

    public function test_patient_edit_normalizes_empty_and_incomplete_history_values()
    {
        $nurse = $this->createStaffUser('Nurse', 'nurse-empty-edit@example.test');
        $patient = Patient::create([
            'id_number' => 'EMPTY-1001',
            'first_name' => 'Empty',
            'last_name' => 'History',
            'status' => 'Active',
            'added_by' => $nurse->id,
        ]);
        HealthExaminationRecord::create([
            'patient_id' => $patient->id,
            'past_medical_history' => null,
            'family_history' => null,
            'social_history' => ['is_smoking' => 'No'],
            'phyiscal_examination' => null,
            'vital_signs' => null,
            'assessment' => null,
            'nursing_interventions' => [
                'nursing_interventions' => [
                    ['intervention' => 'Observation'],
                ],
            ],
            'added_by' => $nurse->id,
        ]);

        $this->actingAs($nurse)
            ->get(route('patients.edit', $patient))
            ->assertStatus(200)
            ->assertSee('Empty')
            ->assertSee('History')
            ->assertSee('Observation');

        $record = $patient->healthExaminationRecord()->firstOrFail();
        $this->assertSame('', $record->social_history['packs_smoked']);
        $this->assertSame([], $record->social_history['medications']);
        $this->assertSame('', $record->nursing_interventions['nursing_interventions'][0]['time']);
        $this->assertSame('', $record->nursing_interventions['nursing_interventions'][0]['by']);
    }

    public function test_review_links_existing_patient_without_creating_duplicate()
    {
        [, $student] = $this->createStudentAccount('2026-1010');
        $complaint = $this->createComplaint($student);
        $nurse = $this->createStaffUser('Nurse', 'nurse-existing@example.test');
        $patient = Patient::create([
            'id_number' => $student->student_id_number,
            'first_name' => 'Existing',
            'last_name' => 'Patient',
            'status' => 'Inactive',
            'added_by' => $nurse->id,
        ]);

        $this->actingAs($nurse)
            ->patch(route('student-complaints.status', $complaint), ['status' => 'Reviewed'])
            ->assertRedirect();

        $this->assertSame(1, Patient::where('id_number', $student->student_id_number)->count());
        $this->assertDatabaseHas('student_complaints', [
            'id' => $complaint->id,
            'patient_id' => $patient->id,
        ]);
        $this->assertDatabaseHas('patients', [
            'id' => $patient->id,
            'status' => 'Active',
            'updated_by' => $nurse->id,
        ]);
        $this->assertTrue(ActivityLog::where('action', 'LIKE', '%linked complaint to existing patient%')->exists());
    }

    public function test_nurse_forwards_and_doctor_completes_consultation_workflow()
    {
        Storage::fake('local');
        [$studentUser, $student] = $this->createStudentAccount('2026-1011');
        $complaint = $this->createComplaint($student);
        $nurse = $this->createStaffUser('Nurse', 'nurse-forward@example.test');
        $counterStaff = $this->createStaffUser('Staff', 'staff-forward@example.test');
        $doctor = $this->createStaffUser('Doctor', 'doctor-intake@example.test');

        $this->actingAs($nurse)
            ->patch(route('student-complaints.status', $complaint), ['status' => 'Reviewed'])
            ->assertRedirect();

        $this->actingAs($nurse)
            ->post(route('student-complaints.forward', $complaint), [
                'service_needed' => 'Medical Consultation',
                'priority' => 'High',
                'nurse_notes' => 'Needs doctor assessment.',
            ])
            ->assertRedirect();

        $complaint->refresh();
        $this->assertDatabaseHas('consultations', [
            'student_complaint_id' => $complaint->id,
            'service_needed' => 'Medical Consultation',
            'priority' => 'High',
            'status' => 'Pending Consultation',
            'forwarded_by' => $nurse->id,
        ]);
        $this->assertDatabaseHas('medical_records', [
            'id' => $complaint->medical_record_id,
            'record_type' => 'Consultation',
            'source' => 'Doctor Consultation',
            'consultation_status' => 'Pending Consultation',
        ]);

        $this->actingAs($nurse)
            ->post(route('student-complaints.start-consultation', $complaint))
            ->assertStatus(403);

        $this->actingAs($doctor)
            ->post(route('student-complaints.start-consultation', $complaint))
            ->assertRedirect();

        $this->assertDatabaseHas('consultations', [
            'student_complaint_id' => $complaint->id,
            'status' => 'In Consultation',
            'doctor_id' => $doctor->id,
        ]);

        $completionResponse = $this->actingAs($doctor)
            ->post(route('student-complaints.complete-consultation', $complaint), [
                'diagnosis' => 'Migraine',
                'treatment' => 'Rest and hydration',
                'prescription_type' => 'Medication',
                'medications' => [[
                    'medication' => 'Biogesic',
                    'dosage' => '500mg',
                    'frequency' => 'Every 6 hours',
                    'duration' => '3 days',
                    'instruction' => 'Take after meals',
                ]],
                'doctor_notes' => 'No neurological deficit.',
                'additional_instructions' => "Drink plenty of water.\nReturn if symptoms worsen.",
            ]);

        $completionResponse->assertSessionHasNoErrors();
        $prescription = \App\Prescription::where('consultation_id', $complaint->consultation->id)->firstOrFail();
        $completionResponse->assertRedirect(route('student-complaints.show', $complaint));

        $this->assertDatabaseHas('medical_records', [
            'id' => $complaint->medical_record_id,
            'consultation_status' => 'Completed',
            'diagnosis' => 'Migraine',
            'medication_taken' => 'Biogesic 500mg',
            'attending_physician' => $doctor->fullName(),
        ]);
        $this->assertSame('Medication', $prescription->prescription_type);
        $this->assertSame('Biogesic', $prescription->medications[0]['medication']);
        Storage::disk('local')->assertExists($prescription->pdf_path);
        $this->assertStringStartsWith('%PDF-', Storage::disk('local')->get($prescription->pdf_path));
        $this->assertDatabaseHas('medical_records', [
            'prescription_id' => $prescription->id,
            'record_type' => 'Prescription',
            'description' => 'Biogesic',
            'outcome' => 'Issued',
        ]);
        $this->assertDatabaseHas('student_complaints', ['id' => $complaint->id, 'status' => 'Completed']);
        $this->assertTrue(ActivityLog::where('action', 'LIKE', '%started consultation%')->exists());
        $this->assertTrue(ActivityLog::where('action', 'LIKE', '%completed consultation%')->exists());
        $this->assertTrue(ActivityLog::where('action', 'LIKE', '%generated prescription%')->exists());

        $notification = \App\ClinicNotification::where('user_id', $nurse->id)
            ->where('related_consultation_id', $complaint->consultation->id)
            ->firstOrFail();
        $this->assertSame('consultation_completed', $notification->type);
        $this->assertFalse($notification->is_read);
        $this->assertStringContainsString('You may now call the next student', $notification->message);
        $this->assertDatabaseHas('notifications', [
            'user_id' => $counterStaff->id,
            'role_target' => 'Staff',
            'related_consultation_id' => $complaint->consultation->id,
            'is_read' => false,
        ]);
        $this->actingAs($nurse)
            ->get(route('notifications.unread'))
            ->assertStatus(200)
            ->assertJsonPath('unread_count', 1)
            ->assertJsonPath('notifications.0.id', $notification->id);
        $this->actingAs($nurse)->post(route('notifications.read', $notification))->assertRedirect();
        $this->assertDatabaseHas('notifications', ['id' => $notification->id, 'is_read' => true]);

        $this->actingAs($studentUser)->get(route('student.prescriptions.index'))->assertStatus(200)->assertSee('Biogesic');
        $this->actingAs($studentUser)->get(route('prescriptions.show', $prescription))->assertStatus(200)->assertSee('Every 6 hours');
        $this->actingAs($studentUser)->get(route('prescriptions.pdf', $prescription))->assertStatus(200)->assertHeader('content-type', 'application/pdf');

        [$otherStudentUser] = $this->createStudentAccount('2026-1099');
        $this->actingAs($otherStudentUser)->get(route('prescriptions.show', $prescription))->assertStatus(403);

        $this->actingAs($doctor)->get(route('prescriptions.print', $prescription))->assertStatus(200);
        $this->assertTrue(ActivityLog::where('action', 'LIKE', '%printed prescription%')->exists());
    }

    public function test_nurse_can_resolve_complaint_at_counter_without_doctor_queue()
    {
        [$studentUser, $student] = $this->createStudentAccount('2026-1040');
        $complaint = $this->createComplaint($student);
        $nurse = $this->createStaffUser('Nurse', 'nurse-counter@example.test');

        $this->actingAs($nurse)
            ->patch(route('student-complaints.status', $complaint), ['status' => 'Reviewed'])
            ->assertRedirect();

        $this->actingAs($nurse)
            ->post(route('student-complaints.resolve-counter', $complaint), [
                'remedy_given' => 'Warm compress provided',
                'quantity' => '1 pack',
                'notes' => 'Observed for fifteen minutes.',
                'outcome' => 'Resolved',
            ])
            ->assertRedirect();

        $complaint->refresh();
        $this->assertDatabaseHas('counter_services', [
            'student_complaint_id' => $complaint->id,
            'remedy_given' => 'Warm compress provided',
            'handled_by' => $nurse->id,
            'outcome' => 'Resolved',
        ]);
        $this->assertDatabaseMissing('consultations', ['student_complaint_id' => $complaint->id]);
        $this->assertDatabaseHas('medical_records', [
            'id' => $complaint->medical_record_id,
            'record_type' => 'Counter Remedy',
            'source' => 'Student Intake / Counter Service',
            'outcome' => 'Resolved',
        ]);
        $this->assertSame('Counter Resolved', $complaint->status);

        $this->actingAs($studentUser)
            ->get(route('student.medical-history'))
            ->assertStatus(200)
            ->assertSee('Counter Remedy')
            ->assertSee('Warm compress provided');
    }

    public function test_nurse_dashboard_prioritizes_and_calls_next_consultation_student()
    {
        $nurse = $this->createStaffUser('Nurse', 'nurse-next-queue@example.test');
        $doctor = $this->createStaffUser('Doctor', 'doctor-next-queue@example.test');
        [$studentUserLow, $lowStudent] = $this->createStudentAccount('2026-1050');
        [, $highStudent] = $this->createStudentAccount('2026-1051');
        $lowComplaint = $this->createComplaint($lowStudent);
        $highComplaint = $this->createComplaint($highStudent);

        foreach ([[$lowComplaint, 'Low'], [$highComplaint, 'High']] as [$complaint, $priority]) {
            $this->actingAs($nurse)->patch(route('student-complaints.status', $complaint), ['status' => 'Reviewed'])->assertRedirect();
            $this->actingAs($nurse)->post(route('student-complaints.forward', $complaint), [
                'service_needed' => 'Medical Consultation',
                'priority' => $priority,
                'nurse_notes' => $priority . ' priority queue test.',
            ])->assertRedirect();
        }

        $lowConsultation = \App\Consultation::where('student_complaint_id', $lowComplaint->id)->firstOrFail();
        $highConsultation = \App\Consultation::where('student_complaint_id', $highComplaint->id)->firstOrFail();
        $lowConsultation->update(['forwarded_at' => now()->subHour()]);

        $this->actingAs($nurse)
            ->get(route('dashboard'))
            ->assertStatus(200)
            ->assertSee('Next Student in Queue')
            ->assertSee($highComplaint->student_name)
            ->assertSee('High Priority');

        $this->actingAs($studentUserLow)
            ->post(route('consultations.call-student', $highConsultation))
            ->assertStatus(403);

        $this->actingAs($nurse)
            ->post(route('consultations.call-student', $highConsultation))
            ->assertRedirect();

        $this->assertDatabaseHas('consultations', [
            'id' => $highConsultation->id,
            'status' => 'Called',
            'called_by' => $nurse->id,
        ]);
        $this->assertTrue(ActivityLog::where('action', 'LIKE', '%called ' . $highComplaint->student_name . ' for consultation%')->exists());

        $this->actingAs($nurse)
            ->get(route('dashboard'))
            ->assertStatus(200)
            ->assertSee($lowComplaint->student_name);

        $this->actingAs($doctor)
            ->post(route('student-complaints.start-consultation', $highComplaint))
            ->assertRedirect();
        $this->assertDatabaseHas('consultations', ['id' => $highConsultation->id, 'status' => 'In Consultation']);
    }

    public function test_medical_record_edit_combines_cast_date_and_time_without_double_time()
    {
        $nurse = $this->createStaffUser('Nurse', 'nurse-medical-record-view@example.test');
        $patient = Patient::create([
            'id_number' => 'MR-2026-001',
            'first_name' => 'Medical',
            'last_name' => 'Record',
            'status' => 'Active',
            'added_by' => $nurse->id,
        ]);
        $record = MedicalRecord::create([
            'patient_id' => $patient->id,
            'date_of_consultation' => '2026-06-03',
            'time_of_consultation' => '15:30:00',
            'chief_complaint' => 'Headache',
            'diagnosis' => 'For observation',
        ]);

        $this->assertSame('2026-06-03 03:30 PM', $record->getDateTimeConsultation());

        $this->actingAs($nurse)
            ->get(route('medical-records.edit', $record))
            ->assertStatus(403);
    }

    public function test_nurse_dashboard_replaces_registered_users_with_student_queue()
    {
        $nurse = $this->createStaffUser('Nurse', 'nurse-dashboard@example.test');

        $this->actingAs($nurse)
            ->get(route('dashboard'))
            ->assertStatus(200)
            ->assertSee('Student Intake Queue')
            ->assertSee('Pending Complaints')
            ->assertSee('Reviewed Complaints')
            ->assertSee('Consultations Today')
            ->assertSee('Patients Today')
            ->assertSee('Clinic Workflow Status')
            ->assertDontSee('Manage Users')
            ->assertDontSee('Clinic Snapshot');

        $this->actingAs($nurse)
            ->get(route('users.index'))
            ->assertRedirect(route('errors.admin'));
    }

    public function test_admin_dashboard_has_four_kpis_and_one_service_chart()
    {
        $admin = $this->createStaffUser('Administrator', 'admin-dashboard@example.test');

        $this->actingAs($admin)
            ->get(route('dashboard'))
            ->assertStatus(200)
            ->assertSee('Total Patients')
            ->assertSee('Pending Complaints')
            ->assertSee('Consultations Today')
            ->assertSee('Active Services')
            ->assertSee('Manage Users')
            ->assertSee('Consultations by Service')
            ->assertSee('Recent Activity')
            ->assertDontSee('Clinic Snapshot')
            ->assertDontSee('Recent Patients');
    }

    public function test_doctor_dashboard_prioritizes_consultations()
    {
        $doctor = $this->createStaffUser('Doctor', 'doctor-dashboard@example.test');

        $this->actingAs($doctor)
            ->get(route('dashboard'))
            ->assertStatus(200)
            ->assertSee('Pending Consultations')
            ->assertSee('In Consultation')
            ->assertSee('Completed Today')
            ->assertSee('Total Patients')
            ->assertSee('Consultation Queue')
            ->assertSee('Medical Records')
            ->assertSee('Recent Consultations')
            ->assertSee('Daily Consultation Trend')
            ->assertDontSee('Manage Users')
            ->assertDontSee('Clinic Snapshot');
    }

    public function test_doctor_can_open_the_medical_records_index()
    {
        $doctor = $this->createStaffUser('Doctor', 'doctor-record-index@example.test');
        $patient = Patient::create([
            'id_number' => 'MR-2026-002',
            'first_name' => 'Index',
            'last_name' => 'Patient',
            'status' => 'Active',
            'added_by' => $doctor->id,
        ]);

        MedicalRecord::create([
            'patient_id' => $patient->id,
            'date_of_consultation' => '2026-06-21',
            'chief_complaint' => 'Persistent headache',
            'attending_physician' => $doctor->fullName(),
        ]);

        $this->actingAs($doctor)
            ->get(route('medical-records.index'))
            ->assertStatus(200)
            ->assertSee('Patient, Index')
            ->assertSee('MR-2026-002')
            ->assertSee('Persistent headache');
    }

    public function test_student_dashboard_only_shows_current_concern_and_clinic_support()
    {
        [$user, $student] = $this->createStudentAccount('2026-1012');
        $complaint = $this->createComplaint($student);
        $this->createStaffUser('Doctor', 'available-doctor@example.test');

        $response = $this->actingAs($user)->get(route('student.dashboard'));

        $response->assertStatus(200)
            ->assertSee('Latest Clinic Concern')
            ->assertSee($complaint->chief_complaint)
            ->assertSee('Available Clinic Staff')
            ->assertSee('Available Services')
            ->assertSee('Important Information')
            ->assertSee('Submit New Concern')
            ->assertDontSee('Previous Complaints')
            ->assertDontSee('Profile Summary')
            ->assertDontSee('Consultations by Service')
            ->assertDontSee('Complaint Status Summary')
            ->assertDontSee('Daily Consultation Trend');
    }

    public function test_student_can_view_complaint_history_and_profile_pages()
    {
        [$user, $student] = $this->createStudentAccount('2026-1013');
        $complaint = $this->createComplaint($student);

        $this->actingAs($user)
            ->get(route('student.complaints.index'))
            ->assertStatus(200)
            ->assertSee('Submit Chief Complaint')
            ->assertSee('My Complaint History')
            ->assertSee($complaint->chief_complaint);

        $this->actingAs($user)
            ->get(route('student.profile'))
            ->assertStatus(200)
            ->assertSee($student->student_id_number)
            ->assertSee($student->full_name)
            ->assertSee($student->college_department)
            ->assertSee($student->present_address);
    }

    public function test_staff_cannot_access_student_portal_pages()
    {
        $nurse = $this->createStaffUser('Nurse', 'nurse-student-pages@example.test');

        foreach ([
            route('student.dashboard'),
            route('student.complaints.index'),
            route('student.profile'),
        ] as $studentUrl) {
            $this->actingAs($nurse)->get($studentUrl)->assertStatus(403);
        }
    }

    private function createStudentAccount($studentId)
    {
        $role = Role::firstOrCreate(['name' => 'Student']);
        $user = User::create([
            'role_id' => $role->id,
            'username' => $studentId,
            'email' => strtolower($studentId) . '@example.test',
            'password' => Hash::make('password123'),
            'first_name' => 'Student',
            'middle_name' => null,
            'last_name' => $studentId,
            'gender' => 'Female',
            'civil_status' => 'Single',
            'age' => 20,
            'birthdate' => '2006-01-15',
            'present_address' => 'Iligan City',
            'home_address' => 'Lanao del Norte',
            'phone_number' => '09170000000',
            'first_login' => false,
            'must_change_password' => false,
        ]);

        $student = Student::create([
            'user_id' => $user->id,
            'student_id_number' => $studentId,
            'first_name' => $user->first_name,
            'middle_name' => $user->middle_name,
            'last_name' => $user->last_name,
            'email' => $user->email,
            'college_department' => 'College of Engineering',
            'contact_number' => '09170000000',
            'gender' => $user->gender,
            'birth_date' => $user->birthdate,
            'age' => $user->age,
            'civil_status' => $user->civil_status,
            'home_address' => $user->home_address,
            'present_address' => $user->present_address,
        ]);

        return [$user, $student];
    }

    private function createStaffUser($roleName, $email)
    {
        $role = Role::firstOrCreate(['name' => $roleName]);

        return User::create([
            'role_id' => $role->id,
            'email' => $email,
            'password' => Hash::make('password123'),
            'first_name' => 'Clinic',
            'middle_name' => null,
            'last_name' => $roleName,
            'status' => 'Active',
            'first_login' => false,
            'must_change_password' => false,
        ]);
    }

    private function createComplaint(Student $student)
    {
        return StudentComplaint::create([
            'student_id' => $student->id,
            'student_id_number' => $student->student_id_number,
            'student_name' => $student->full_name,
            'complaint_category' => 'General Consultation',
            'chief_complaint' => 'Headache',
            'symptoms_description' => 'Headache since this morning.',
            'urgency_level' => 'Moderate',
            'status' => 'Pending',
            'submitted_at' => now(),
        ]);
    }

    private function registrationData($studentId, $email)
    {
        return [
            'student_id_number' => $studentId,
            'first_name' => 'Registration',
            'middle_name' => 'Test',
            'last_name' => 'Student',
            'email' => $email,
            'password' => 'password123',
            'password_confirmation' => 'password123',
            'college_department' => 'College of Engineering',
            'contact_number' => '09171234567',
            'gender' => 'Female',
            'birth_date' => '2006-02-14',
            'age' => 20,
            'civil_status' => 'Single',
            'home_address' => 'Lanao del Norte',
            'present_address' => 'Iligan City',
        ];
    }
}
