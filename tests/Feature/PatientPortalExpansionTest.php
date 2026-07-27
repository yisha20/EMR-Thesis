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
use Tests\TestCase;

class PatientPortalExpansionTest extends TestCase
{
    use DatabaseTransactions;

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

    public function test_selected_login_type_must_match_database_account_type()
    {
        [$user]=$this->patientUser('faculty');
        $this->post(route('login'),['account_type'=>'student','email'=>$user->email,'password'=>'password123'])
            ->assertSessionHasErrors('account_type');
        $this->assertGuest();
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
