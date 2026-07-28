<?php

namespace Tests\Feature;

use App\ClinicQueue;
use App\HealthAssessment;
use App\Patient;
use App\CommonComplaintOption;
use App\PatientAccount;
use App\Role;
use App\Services\ClinicQueueService;
use App\Student;
use App\StudentComplaint;
use App\User;
use Illuminate\Foundation\Testing\DatabaseTransactions;
use Illuminate\Support\Facades\Hash;
use App\ClinicNotification;
use Carbon\Carbon;
use Illuminate\Validation\ValidationException;
use Tests\TestCase;
use Illuminate\Support\Facades\Notification;

class PatientPortalExpansionTest extends TestCase
{
    use DatabaseTransactions;

    public function test_login_form_is_unified_and_has_recovery_and_remember_controls()
    {
        $this->get(route('login'))->assertOk()
            ->assertDontSee('Account Type')
            ->assertSee('Remember Me')
            ->assertSee('Forgot Password?');
    }

    public function test_password_recovery_response_does_not_disclose_account_existence()
    {
        Notification::fake();
        [$user] = $this->patientUser('student', 'recovery-generic');
        $message = 'If an account matches that email address, a password reset link has been sent.';
        $this->post(route('auth.send_code'), ['email' => $user->email])
            ->assertSessionHas('success', $message);
        $this->post(route('auth.send_code'), ['email' => 'absent-'.uniqid().'@example.test'])
            ->assertSessionHas('success', $message);
    }

    public function test_remember_me_uses_framework_remember_token()
    {
        [$user] = $this->patientUser('student', 'remember-user');
        $user->forceFill(['remember_token' => null])->save();
        $this->post(route('login'), [
            'email' => $user->email, 'password' => 'password123', 'remember' => '1',
        ])->assertRedirect();
        $this->assertNotEmpty($user->fresh()->remember_token);
    }

    public function test_new_student_and_faculty_accounts_are_gated_by_health_assessment()
    {
        Role::firstOrCreate(['name'=>'Student']);
        foreach (['student','faculty'] as $type) {
            $id=$type.'-'.uniqid();
            $payload=['account_type'=>$type,'student_id_number'=>$type==='student'?$id:null,'faculty_id_number'=>$type==='faculty'?$id:null,
                'first_name'=>'Portal','last_name'=>ucfirst($type),'email'=>$id.'@example.test','password'=>'password123',
                'password_confirmation'=>'password123','college_department'=>'Clinic Test','contact_number'=>'09170000000'];
            $this->post(route('student.register.store'),$payload)->assertRedirect(route('login'));
            $user=User::where('email',$payload['email'])->firstOrFail();
            $this->assertSame($type,$user->patientAccount->patient_type);
            $this->actingAs($user)->get(route('student.dashboard'))->assertRedirect(route('patient.assessment.edit'));
            $this->app['auth']->logout();
        }
    }

    public function test_unified_login_ignores_client_account_type_and_uses_stored_account_type()
    {
        [$user]=$this->patientUser('faculty');
        $this->post(route('login'),['account_type'=>'student','email'=>$user->email,'password'=>'password123'])
            ->assertRedirect(route('patient.assessment.edit'));
        $this->assertAuthenticatedAs($user);
        $this->assertSame('faculty', $user->fresh()->patientAccount->patient_type);
    }

    public function test_complaint_has_optional_details_and_patient_cannot_assign_priority()
    {
        [$user,$student,$account]=$this->patientUser('student','patient-complaint');
        $account->update(['health_assessment_status'=>'patient_submitted']);
        $option=CommonComplaintOption::where('name','Headache')->firstOrFail();
        $this->actingAs($user)->post(route('student.complaints.store'),[
            'complaint_options'=>[$option->id],'urgency_level'=>'High',
        ])->assertRedirect(route('student.complaints.index'));
        $complaint=StudentComplaint::latest('id')->first();
        $this->assertSame('',$complaint->symptoms_description);
        $this->assertSame('Unassigned',$complaint->urgency_level);
        $this->assertSame('unassigned',$complaint->triage_priority);
    }

    public function test_queue_ticket_is_idempotent_and_patient_status_is_privacy_safe()
    {
        [$user,$student,$account]=$this->patientUser('student','queue-owner');
        $account->update(['health_assessment_status'=>'patient_submitted']);
        $complaint=StudentComplaint::create(['student_id'=>$student->id,'patient_account_id'=>$account->id,
            'student_id_number'=>$student->student_id_number,'student_name'=>$student->full_name,'chief_complaint'=>'Private concern',
            'symptoms_description'=>'','urgency_level'=>'Unassigned','triage_priority'=>'high','status'=>'Reviewed','submitted_at'=>now()]);
        $service=app(ClinicQueueService::class);
        $first=$service->enqueue($complaint,'counter','high',$user->id);
        $second=$service->enqueue($complaint,'counter','high',$user->id);
        $this->assertSame($first->id,$second->id);
        $this->assertSame(1,ClinicQueue::where('student_complaint_id',$complaint->id)->count());
        $response=$this->actingAs($user)->getJson(route('patient.queue.status'))->assertOk();
        $response->assertJsonMissing(['complaint'=>'Private concern'])->assertJsonFragment(['ticket'=>$first->ticket_number]);
    }

    public function test_other_concern_is_conditional_and_review_uses_only_digital_queue_wording()
    {
        [$user,$student,$account]=$this->patientUser('student','conditional-other');
        $account->update(['health_assessment_status'=>'patient_submitted']);
        $other=CommonComplaintOption::where('name','Other')->firstOrFail();
        $this->actingAs($user)->post(route('student.complaints.store'),['complaint_options'=>[$other->id]])
            ->assertSessionHasErrors('other_complaint');
        $this->get(route('student.complaints.index'))->assertOk()
            ->assertSee('Others:')
            ->assertDontSee('urgency_level');

        $complaint=StudentComplaint::create(['student_id'=>$student->id,'patient_account_id'=>$account->id,
            'student_id_number'=>$student->student_id_number,'student_name'=>$student->full_name,'chief_complaint'=>'Other',
            'other_complaint'=>'Private detail','symptoms_description'=>'','urgency_level'=>'Unassigned',
            'triage_priority'=>'unassigned','status'=>'Reviewed','submitted_at'=>now()]);
        $nurse=$this->staffUser('Nurse','digital-queue-review');
        $this->actingAs($nurse)->get(route('student-complaints.show',$complaint))->assertOk()
            ->assertSee('Add to Counter Queue')->assertSee('Forward to Consultation Queue')
            ->assertDontSee('Issue Queue Ticket')->assertDontSee('Queue Ticket');
    }

    public function test_staff_routing_assigns_daily_counter_and_consultation_numbers_once()
    {
        [$owner,$student,$account]=$this->patientUser('student','route-owner');
        $patient=Patient::create(['id_number'=>$student->student_id_number,'first_name'=>'Route','last_name'=>'Owner',
            'type'=>'Student','status'=>'Active','added_by'=>$owner->id]);
        $account->update(['patient_id'=>$patient->id,'health_assessment_status'=>'patient_submitted']);
        $nurse=$this->staffUser('Nurse','queue-router');
        foreach(['counter'=>'C-','consultation'=>'D-'] as $type=>$prefix){
            $complaint=StudentComplaint::create(['student_id'=>$student->id,'patient_account_id'=>$account->id,'patient_id'=>$patient->id,
                'student_id_number'=>$student->student_id_number,'student_name'=>$student->full_name,'chief_complaint'=>'Routing test',
                'symptoms_description'=>'','urgency_level'=>'Unassigned','triage_priority'=>'unassigned','status'=>'Reviewed','submitted_at'=>now()]);
            $this->actingAs($nurse)->post(route('clinic-queues.store',$complaint),['queue_type'=>$type,'priority'=>'high'])->assertRedirect();
            $queue=ClinicQueue::where('student_complaint_id',$complaint->id)->firstOrFail();
            $this->assertStringStartsWith($prefix,$queue->ticket_number);
            $this->actingAs($owner)->get(route('student.dashboard'))->assertOk()
                ->assertSee($queue->ticket_number);
            $this->getJson(route('patient.queue.status'))->assertOk()
                ->assertJsonFragment(['ticket'=>$queue->ticket_number]);
            $this->actingAs($nurse)->post(route('clinic-queues.store',$complaint),['queue_type'=>$type,'priority'=>'high'])->assertRedirect();
            $this->assertSame(1,ClinicQueue::where('student_complaint_id',$complaint->id)->where('queue_type',$type)->count());
        }
    }

    public function test_pdf_handles_missing_photo_and_enforces_ownership()
    {
        [$owner,, $account]=$this->patientUser('student','pdf-owner');
        $assessment=HealthAssessment::create(['patient_account_id'=>$account->id,'patient_type'=>'student','status'=>'patient_submitted',
            'version'=>1,'personal_information'=>['first_name'=>'PDF','last_name'=>'Owner','birth_date'=>'2000-01-01','age'=>26]]);
        $this->actingAs($owner)->get(route('health-assessments.pdf',$assessment))->assertOk()
            ->assertHeader('content-type','application/pdf');
        [$other]=$this->patientUser('student','pdf-other');
        $this->actingAs($other)->get(route('health-assessments.pdf',$assessment))->assertStatus(403);
        $html=view('patient.assessments.pdf',compact('assessment'))->render();
        $this->assertStringContainsString('No photo provided',$html);
        $this->assertStringContainsString('For clinic staff completion',$html);
        $this->assertStringContainsString('Physical Examination',$html);
    }

    public function test_alternating_dispatch_is_priority_protected_and_persists_last_type()
    {
        [$owner,$student,$account]=$this->patientUser('student','dispatch-owner');
        $service=app(ClinicQueueService::class);
        $make=function($type,$priority,$label)use($student,$account,$owner,$service){
            $complaint=StudentComplaint::create(['student_id'=>$student->id,'patient_account_id'=>$account->id,
                'student_id_number'=>$student->student_id_number,'student_name'=>$student->full_name,
                'chief_complaint'=>$label,'symptoms_description'=>'','urgency_level'=>'Unassigned',
                'triage_priority'=>$priority,'status'=>'Reviewed','submitted_at'=>now()]);
            return $service->enqueue($complaint,$type,$priority,$owner->id);
        };
        $counter=$make('counter','low','Counter low');
        $doctor=$make('consultation','high','Doctor high');
        $this->assertSame($doctor->id,$service->nextCandidate()->id);
        $service->callNext($owner->id);
        $service->transition($doctor->fresh(),'serving',$owner->id);
        $doctorTwo=$make('consultation','low','Doctor low');
        $this->assertSame($counter->id,$service->nextCandidate()->id);
        $service->callNext($owner->id);
        $this->assertSame($doctorTwo->id,$service->nextCandidate()->id);
    }

    public function test_routed_complaint_moves_operations_to_dashboard()
    {
        [$owner,$student,$account]=$this->patientUser('student','review-cleanup');
        $complaint=StudentComplaint::create(['student_id'=>$student->id,'patient_account_id'=>$account->id,
            'student_id_number'=>$student->student_id_number,'student_name'=>$student->full_name,'chief_complaint'=>'Review cleanup',
            'symptoms_description'=>'','urgency_level'=>'Unassigned','triage_priority'=>'low','status'=>'Reviewed','submitted_at'=>now()]);
        app(ClinicQueueService::class)->enqueue($complaint,'counter','low',$owner->id);
        $nurse=$this->staffUser('Nurse','review-cleanup-nurse');
        $this->actingAs($nurse)->get(route('student-complaints.show',$complaint))->assertOk()
            ->assertSee('Open Queue Dashboard')->assertDontSee('Call / Recall')->assertDontSee('Mark Missed');
        $this->get(route('dashboard'))->assertOk()->assertSee('Shared Clinic Queue')->assertSee('Call Next');
    }

    public function test_new_complaint_notifies_each_nurse_once_and_refresh_does_not_duplicate()
    {
        $nurse=$this->staffUser('Nurse','notify-new-complaint');
        [$owner,, $account]=$this->patientUser('student','notify-complaint-owner');
        $account->update(['health_assessment_status'=>'patient_submitted']);
        $option=CommonComplaintOption::where('name','Headache')->firstOrFail();
        $this->actingAs($owner)->post(route('student.complaints.store'),['complaint_options'=>[$option->id]])->assertRedirect();
        $complaint=StudentComplaint::latest('id')->firstOrFail();
        $this->assertSame(1,ClinicNotification::where('user_id',$nurse->id)
            ->where('notification_type','new_patient_complaint')->where('related_complaint_id',$complaint->id)->count());
        $this->get(route('student.complaints.index'))->assertOk();
        $this->assertSame(1,ClinicNotification::where('user_id',$nurse->id)
            ->where('notification_type','new_patient_complaint')->where('related_complaint_id',$complaint->id)->count());
    }

    public function test_consultation_routing_notifies_doctor_once()
    {
        [$owner,$student,$account]=$this->patientUser('student','notify-doctor-owner');
        $patient=Patient::create(['id_number'=>$student->student_id_number,'first_name'=>'Doctor','last_name'=>'Notice',
            'type'=>'Student','status'=>'Active','added_by'=>$owner->id]);
        $account->update(['patient_id'=>$patient->id]);
        $nurse=$this->staffUser('Nurse','notify-doctor-nurse');
        $doctor=$this->staffUser('Doctor','notify-forwarded-doctor');
        $complaint=StudentComplaint::create(['student_id'=>$student->id,'patient_account_id'=>$account->id,'patient_id'=>$patient->id,
            'student_id_number'=>$student->student_id_number,'student_name'=>$student->full_name,'chief_complaint'=>'Consultation',
            'symptoms_description'=>'','urgency_level'=>'Unassigned','triage_priority'=>'high','status'=>'Reviewed','submitted_at'=>now()]);
        $this->actingAs($nurse)->post(route('clinic-queues.store',$complaint),['queue_type'=>'consultation','priority'=>'high'])->assertRedirect();
        $this->actingAs($nurse)->post(route('clinic-queues.store',$complaint),['queue_type'=>'consultation','priority'=>'high'])->assertRedirect();
        $this->assertSame(1,ClinicNotification::where('user_id',$doctor->id)
            ->where('notification_type','patient_forwarded_to_consultation')
            ->where('related_complaint_id',$complaint->id)->count());
    }

    public function test_presence_keeps_position_and_notification_access_is_private()
    {
        [$owner,$student,$account]=$this->patientUser('student','presence-owner');
        $account->update(['health_assessment_status'=>'patient_submitted']);
        $complaint=StudentComplaint::create(['student_id'=>$student->id,'patient_account_id'=>$account->id,
            'student_id_number'=>$student->student_id_number,'student_name'=>$student->full_name,'chief_complaint'=>'Presence',
            'symptoms_description'=>'','urgency_level'=>'Unassigned','triage_priority'=>'low','status'=>'Reviewed','submitted_at'=>now()]);
        $queue=app(ClinicQueueService::class)->enqueue($complaint,'counter','low',$owner->id);
        $position=$queue->position;
        $this->actingAs($owner)->postJson(route('patient.queue.presence',$queue),['presence_status'=>'temporarily_away'])
            ->assertOk()->assertJson(['presence_status'=>'temporarily_away']);
        $this->assertSame($position,$queue->fresh()->position);
        [$other,, $otherAccount]=$this->patientUser('student','presence-other');
        $otherAccount->update(['health_assessment_status'=>'patient_submitted']);
        $this->actingAs($other)->postJson(route('patient.queue.presence',$queue),['presence_status'=>'returning'])->assertStatus(403);
        $notice=ClinicNotification::where('user_id',$owner->id)->firstOrFail();
        $this->post(route('notifications.read',$notice))->assertStatus(403);
    }

    public function test_position_call_recall_and_acknowledgement_notifications_are_deduplicated()
    {
        Carbon::setTestNow(now()->startOfMinute());
        [$owner,$student,$account]=$this->patientUser('student','queue-event-owner');
        $account->update(['health_assessment_status'=>'patient_submitted']);
        $make=function($label)use($student,$account,$owner){
            $complaint=StudentComplaint::create(['student_id'=>$student->id,'patient_account_id'=>$account->id,
                'student_id_number'=>$student->student_id_number,'student_name'=>$student->full_name,'chief_complaint'=>$label,
                'symptoms_description'=>'','urgency_level'=>'Unassigned','triage_priority'=>'low','status'=>'Reviewed','submitted_at'=>now()]);
            return app(ClinicQueueService::class)->enqueue($complaint,'counter','low',$owner->id);
        };
        $first=$make('First');$second=$make('Second');
        $this->assertSame(1,ClinicNotification::where('related_queue_id',$first->id)->where('notification_type','patient_next_in_queue')->count());
        $this->assertSame(1,ClinicNotification::where('related_queue_id',$second->id)->where('notification_type','patient_nearly_next')->count());
        $service=app(ClinicQueueService::class);$service->transition($first,'called',$owner->id);
        $this->assertSame(1,ClinicNotification::where('related_queue_id',$first->id)->where('notification_type','patient_called')->count());
        $this->actingAs($owner)->postJson(route('patient.queue.acknowledge',$first))->assertOk();
        $this->assertNotNull($first->fresh()->patient_acknowledged_at);
        Carbon::setTestNow(now()->addMinutes(config('clinic_queue.call_grace_minutes')));
        $service->transition($first->fresh(),'called',$owner->id);
        $this->assertSame(1,$first->fresh()->recall_count);
        $this->assertSame(1,ClinicNotification::where('related_queue_id',$first->id)->where('notification_type','patient_recalled')->count());
        Carbon::setTestNow(now()->addMinutes(config('clinic_queue.call_grace_minutes')));
        $service->transition($first->fresh(),'called',$owner->id);
        $this->assertSame(config('clinic_queue.max_recalls'),$first->fresh()->recall_count);
        try {$service->transition($first->fresh(),'called',$owner->id);$this->fail('Recall limit was not enforced.');}
        catch (ValidationException $exception) {$this->assertArrayHasKey('status',$exception->errors());}
        Carbon::setTestNow(now()->addMinutes(config('clinic_queue.call_grace_minutes')));
        $service->transition($first->fresh(),'missed',$owner->id,'Did not respond');
        $this->assertSame('Did not respond',$first->fresh()->missed_reason);
        $this->assertDatabaseHas('student_complaints',['id'=>$first->student_complaint_id]);
        Carbon::setTestNow();
    }

    public function test_two_call_next_attempts_do_not_open_two_active_calls()
    {
        [$owner,$student,$account]=$this->patientUser('student','concurrent-call-owner');
        foreach (['One','Two'] as $label) {
            $complaint=StudentComplaint::create(['student_id'=>$student->id,'patient_account_id'=>$account->id,
                'student_id_number'=>$student->student_id_number,'student_name'=>$student->full_name,'chief_complaint'=>$label,
                'symptoms_description'=>'','urgency_level'=>'Unassigned','triage_priority'=>'low','status'=>'Reviewed','submitted_at'=>now()]);
            app(ClinicQueueService::class)->enqueue($complaint,'counter','low',$owner->id);
        }
        $service=app(ClinicQueueService::class);$service->callNext($owner->id);
        try {$service->callNext($owner->id);$this->fail('A second active call was allowed.');}
        catch (ValidationException $exception) {$this->assertStringContainsString('already being called',$exception->errors()['queue'][0]);}
        $this->assertSame(1,ClinicQueue::where('queue_date',now()->toDateString())->where('status','called')->count());
    }

    private function patientUser($type,$key=null)
    {
        $key=$key?:uniqid($type,true);$role=Role::firstOrCreate(['name'=>'Student']);
        $user=User::create(['role_id'=>$role->id,'username'=>$key,'name'=>'Patient '.$key,'status'=>'Active','email'=>$key.'@example.test',
            'password'=>Hash::make('password123'),'first_name'=>'Patient','last_name'=>$key,'first_login'=>false,'must_change_password'=>false]);
        $student=Student::create(['user_id'=>$user->id,'student_id_number'=>$key,'college_department'=>'Test','contact_number'=>'09170000000','first_name'=>'Patient','last_name'=>$key,'email'=>$user->email]);
        $account=PatientAccount::create(['user_id'=>$user->id,'patient_type'=>$type,
            'student_id_number'=>$type==='student'?$key:null,'faculty_id_number'=>$type==='faculty'?$key:null,
            'verification_status'=>'verified','health_assessment_status'=>'not_started']);
        return [$user,$student,$account];
    }

    private function staffUser($roleName,$key)
    {
        $role=Role::firstOrCreate(['name'=>$roleName]);
        return User::create(['role_id'=>$role->id,'username'=>$key,'name'=>$roleName.' User','status'=>'Active',
            'email'=>$key.'@example.test','password'=>Hash::make('password123'),'first_name'=>$roleName,
            'last_name'=>'User','first_login'=>false,'must_change_password'=>false]);
    }
}
