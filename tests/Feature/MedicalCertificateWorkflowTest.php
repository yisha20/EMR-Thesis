<?php

namespace Tests\Feature;

use App\MedicalCertificate;
use App\Patient;
use App\PatientAccount;
use App\Role;
use App\Consultation;
use App\Student;
use App\StudentComplaint;
use App\User;
use Illuminate\Foundation\Testing\DatabaseTransactions;
use Illuminate\Support\Facades\Hash;
use Tests\TestCase;

class MedicalCertificateWorkflowTest extends TestCase
{
    use DatabaseTransactions;

    public function test_authorized_clinical_users_and_owner_can_view_print_and_download_issued_certificate()
    {
        $doctor = $this->user('Doctor', 'certificate-doctor');
        $nurse = $this->user('Nurse', 'certificate-nurse');
        [$patientUser, $patient] = $this->patientUser('certificate-owner');
        $certificate = $this->certificate($doctor, $patient, 'issued');

        $this->actingAs($doctor)->get(route('medical-certificates.show', $certificate))
            ->assertOk()->assertSee($certificate->certificate_number)->assertSee($certificate->patient_name_snapshot);
        $this->actingAs($nurse)->get(route('medical-certificates.print', $certificate))
            ->assertOk()->assertSee($certificate->certificate_number)->assertSee($doctor->first_name)->assertDontSee('emr-navbar');
        $this->actingAs($patientUser)->get(route('medical-certificates.show', $certificate))
            ->assertOk()->assertSee($certificate->patient_name_snapshot);

        $pdf = $this->actingAs($doctor)->get(route('medical-certificates.pdf', $certificate));
        $pdf->assertOk();
        $this->assertStringContainsString('application/pdf', $pdf->headers->get('content-type'));
        $this->assertStringContainsString('Medical-Certificate-'.$certificate->certificate_number.'.pdf', $pdf->headers->get('content-disposition'));
        $this->assertStringStartsWith('%PDF-', $pdf->getContent());
        $this->assertSame(1, MedicalCertificate::whereKey($certificate->id)->count());
    }

    public function test_unrelated_patient_cannot_access_certificate()
    {
        $doctor = $this->user('Doctor', 'certificate-private-doctor');
        [, $patient] = $this->patientUser('certificate-private-owner');
        [$unrelated] = $this->patientUser('certificate-unrelated');
        $certificate = $this->certificate($doctor, $patient, 'issued');

        $this->actingAs($unrelated)->get(route('medical-certificates.show', $certificate))->assertForbidden();
        $this->actingAs($unrelated)->get(route('medical-certificates.print', $certificate))->assertForbidden();
        $this->actingAs($unrelated)->get(route('medical-certificates.pdf', $certificate))->assertForbidden();
    }

    public function test_only_issuing_doctor_can_view_or_edit_a_draft()
    {
        $doctor = $this->user('Doctor', 'certificate-draft-doctor');
        $otherDoctor = $this->user('Doctor', 'certificate-other-doctor');
        $nurse = $this->user('Nurse', 'certificate-draft-nurse');
        [, $patient] = $this->patientUser('certificate-draft-owner');
        $certificate = $this->certificate($doctor, $patient, 'draft');

        $this->actingAs($doctor)->get(route('medical-certificates.show', $certificate))->assertOk()->assertSee('Draft preview');
        $this->actingAs($doctor)->get(route('medical-certificates.edit', $certificate))->assertOk()->assertSee('Fitness Assessment');
        $this->actingAs($otherDoctor)->get(route('medical-certificates.show', $certificate))->assertForbidden();
        $this->actingAs($nurse)->get(route('medical-certificates.show', $certificate))->assertForbidden();
        $this->actingAs($doctor)->get(route('medical-certificates.print', $certificate))->assertForbidden();
    }

    public function test_certificate_fields_save_and_issued_certificate_is_read_only()
    {
        $doctor = $this->user('Doctor', 'certificate-save-doctor');
        [, $patient] = $this->patientUser('certificate-save-owner');
        $certificate = $this->certificate($doctor, $patient, 'draft');
        $payload = $this->validPayload([
            'reason_for_visit' => 'Pre-employment examination',
            'physical_examination_performed' => '1',
            'fitness_status' => 'fit_with_restrictions',
            'fitness_details' => 'Avoid strenuous activity for seven days.',
            'purpose' => 'employment',
        ]);

        $this->actingAs($doctor)->put(route('medical-certificates.update', $certificate), $payload)->assertRedirect();
        $certificate->refresh();
        $this->assertSame('Pre-employment examination', $certificate->reason_for_visit);
        $this->assertTrue((bool) $certificate->consultation_performed);
        $this->assertTrue((bool) $certificate->physical_examination_performed);
        $this->assertSame('fit_with_restrictions', $certificate->fitness_status);
        $this->assertSame('employment', $certificate->purpose);

        $this->actingAs($doctor)->post(route('medical-certificates.issue', $certificate), ['confirm_issue' => '1'])
            ->assertRedirect(route('medical-certificates.show', $certificate));
        $this->assertSame('issued', $certificate->fresh()->status);
        $this->actingAs($doctor)->put(route('medical-certificates.update', $certificate), $payload)->assertStatus(422);
    }

    public function test_placeholder_impression_and_missing_conditional_details_are_rejected()
    {
        $doctor = $this->user('Doctor', 'certificate-validation-doctor');
        [, $patient] = $this->patientUser('certificate-validation-owner');
        $certificate = $this->certificate($doctor, $patient, 'draft');

        $this->actingAs($doctor)->from(route('medical-certificates.edit', $certificate))
            ->put(route('medical-certificates.update', $certificate), $this->validPayload(['clinical_impression' => 'none']))
            ->assertSessionHasErrors('clinical_impression');
        $this->actingAs($doctor)->from(route('medical-certificates.edit', $certificate))
            ->put(route('medical-certificates.update', $certificate), $this->validPayload(['fitness_status' => 'fit_with_restrictions', 'fitness_details' => '']))
            ->assertSessionHasErrors('fitness_details');
        $this->actingAs($doctor)->from(route('medical-certificates.edit', $certificate))
            ->put(route('medical-certificates.update', $certificate), $this->validPayload(['purpose' => 'other', 'purpose_other' => '']))
            ->assertSessionHasErrors('purpose_other');
    }

    public function test_health_check_reports_runtime_requirements()
    {
        $result = $this->artisan('clinic:health-check');

        if (extension_loaded('gd')) {
            $result->expectsOutput('[PASS] PHP GD extension')->assertExitCode(0);
        } else {
            $result->expectsOutput('[FAIL] PHP GD extension')->assertExitCode(1);
        }
    }

    private function certificate(User $doctor, Patient $patient, $status)
    {
        $account = PatientAccount::where('patient_id', $patient->id)->firstOrFail();
        $student = Student::where('user_id', $account->user_id)->firstOrFail();
        $complaint = StudentComplaint::create([
            'student_id' => $student->id,
            'patient_id' => $patient->id,
            'patient_account_id' => $account->id,
            'student_id_number' => $student->student_id_number,
            'student_name' => $student->full_name,
            'chief_complaint' => 'Clinic consultation',
            'symptoms_description' => 'Test symptoms',
            'urgency_level' => 'Unassigned',
            'triage_priority' => 'low',
            'status' => 'Completed',
            'submitted_at' => now(),
        ]);
        $consultation = Consultation::create([
            'student_complaint_id' => $complaint->id,
            'patient_id' => $patient->id,
            'service_needed' => 'Medical Consultation',
            'priority' => 'Low',
            'forwarded_by' => $doctor->id,
            'forwarded_at' => now()->subHour(),
            'doctor_id' => $doctor->id,
            'status' => 'Completed',
            'completed_at' => now(),
        ]);

        return MedicalCertificate::create([
            'certificate_number' => 'MC-'.now()->format('Ymd').'-'.uniqid(),
            'patient_id' => $patient->id,
            'consultation_id' => $consultation->id,
            'issued_by_doctor_id' => $doctor->id,
            'issue_date' => today(),
            'patient_name_snapshot' => $patient->first_name.' '.$patient->last_name,
            'patient_id_snapshot' => $patient->id_number,
            'age_snapshot' => 22,
            'sex_snapshot' => 'Female',
            'address_snapshot' => 'Iligan City',
            'reason_for_visit' => 'Clinic consultation',
            'consultation_performed' => true,
            'physical_examination_performed' => false,
            'clinical_impression' => 'Upper respiratory tract infection',
            'fitness_status' => 'physically_fit',
            'purpose' => 'ojt',
            'doctor_name_snapshot' => $doctor->fullName(),
            'doctor_license_number_snapshot' => $doctor->license_number,
            'status' => $status,
            'issued_at' => $status === 'issued' ? now() : null,
        ]);
    }

    private function validPayload(array $overrides = [])
    {
        return array_merge([
            'reason_for_visit' => 'Clinic consultation',
            'consultation_performed' => '1',
            'clinical_impression' => 'Upper respiratory tract infection',
            'fitness_status' => 'physically_fit',
            'fitness_details' => '',
            'purpose' => 'ojt',
            'purpose_other' => '',
            'remarks' => '',
            'valid_from' => now()->format('Y-m-d'),
            'valid_until' => now()->addDay()->format('Y-m-d'),
        ], $overrides);
    }

    private function user($roleName, $key)
    {
        $role = Role::firstOrCreate(['name' => $roleName]);
        return User::create([
            'role_id' => $role->id,
            'username' => $key,
            'name' => $roleName.' User',
            'status' => 'Active',
            'email' => $key.'@example.test',
            'password' => Hash::make('password123'),
            'first_name' => $roleName,
            'last_name' => 'User',
            'license_number' => $roleName === 'Doctor' ? 'PRC-123456' : null,
            'first_login' => false,
            'must_change_password' => false,
        ]);
    }

    private function patientUser($key)
    {
        $user = $this->user('Student', $key);
        $patient = Patient::create([
            'id_number' => strtoupper($key),
            'first_name' => 'Karyl',
            'last_name' => 'Oliveros',
            'gender' => 'Female',
            'birthdate' => '2004-01-01',
            'age' => 22,
            'present_address' => 'Iligan City',
            'type' => 'Student',
            'status' => 'Active',
            'added_by' => $user->id,
        ]);
        Student::create([
            'user_id' => $user->id,
            'student_id_number' => $patient->id_number,
            'first_name' => $patient->first_name,
            'last_name' => $patient->last_name,
            'email' => $user->email,
            'college_department' => 'Clinic Test',
            'contact_number' => '09170000000',
        ]);
        PatientAccount::create([
            'user_id' => $user->id,
            'patient_id' => $patient->id,
            'patient_type' => 'student',
            'student_id_number' => $patient->id_number,
            'verification_status' => 'verified',
            'health_assessment_status' => 'patient_submitted',
        ]);

        return [$user, $patient];
    }
}
