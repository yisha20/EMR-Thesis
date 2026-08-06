<?php

namespace Tests\Feature;

use App\Role;
use App\Services\ClinicMonitoringService;
use App\Services\WorkflowMonitor;
use App\SystemIncident;
use App\User;
use App\WorkflowActionLog;
use Illuminate\Foundation\Testing\DatabaseTransactions;
use Tests\TestCase;

class SystemMonitoringTest extends TestCase
{
    use DatabaseTransactions;

    private function user($role)
    {
        $roleRecord = Role::firstOrCreate(['name'=>$role]);
        return User::create(['role_id'=>$roleRecord->id,'name'=>$role.' Monitor','first_name'=>$role,'last_name'=>'Monitor','email'=>strtolower($role).uniqid().'@example.test','password'=>bcrypt('password'),'status'=>'Active']);
    }

    public function test_only_administrator_can_access_monitoring_dashboard()
    {
        $this->actingAs($this->user('Administrator'))->get(route('admin.monitoring.index'))->assertOk()->assertSee('Testing')->assertSee('System Monitoring');
        foreach (['Nurse','Doctor','Patient'] as $role) {
            $this->actingAs($this->user($role))->get(route('admin.monitoring.index'))->assertForbidden();
        }
    }

    public function test_monitor_command_no_write_does_not_create_incidents_or_change_clinical_counts()
    {
        $beforeIncidents=SystemIncident::count();
        $beforeQueues=\App\ClinicQueue::count();
        $beforeRecords=\App\MedicalRecord::count();
        $this->artisan('clinic:monitor',['--full'=>true,'--no-write'=>true])->expectsOutput('Monitoring checks completed.');
        $this->assertSame($beforeIncidents,SystemIncident::count());
        $this->assertSame($beforeQueues,\App\ClinicQueue::count());
        $this->assertSame($beforeRecords,\App\MedicalRecord::count());
    }

    public function test_workflow_monitor_records_results_and_redacts_sensitive_values()
    {
        $this->actingAs($this->user('Administrator'));
        $monitor=app(WorkflowMonitor::class);
        $monitor->attempted('test_action');
        $monitor->succeeded('test_action');
        $reference=$monitor->failed('test_action',new \RuntimeException('password=secret SOAP=private diagnosis=hidden'));
        $this->assertRegExp('/^ERR-\d{8}-\d{6}-[A-Z0-9]{6}$/',$reference);
        $this->assertDatabaseHas('workflow_action_logs',['action_name'=>'test_action','result'=>'attempted']);
        $this->assertDatabaseHas('workflow_action_logs',['action_name'=>'test_action','result'=>'succeeded']);
        $this->assertDatabaseHas('workflow_action_logs',['action_name'=>'test_action','result'=>'failed','error_reference'=>$reference]);
        $technical=SystemIncident::where('reference_code',$reference)->value('technical_message');
        $this->assertStringNotContainsString('secret',$technical);
        $this->assertStringNotContainsString('private',$technical);
        $this->assertStringNotContainsString('hidden',$technical);
    }

    public function test_repeated_integrity_incident_is_deduplicated_and_status_can_be_updated()
    {
        $admin=$this->user('Administrator');
        $monitor=app(WorkflowMonitor::class);
        $first=$monitor->createIncident(['category'=>'queue','event_type'=>'test_duplicate','deduplication_key'=>'test:duplicate','safe_message'=>'Test queue issue.']);
        $second=$monitor->createIncident(['category'=>'queue','event_type'=>'test_duplicate','deduplication_key'=>'test:duplicate','safe_message'=>'Test queue issue.']);
        $this->assertSame($first->id,$second->id);
        $this->actingAs($admin)->patch(route('admin.monitoring.incidents.status',$first),['status'=>'investigating'])->assertRedirect(route('admin.monitoring.index'));
        $this->assertDatabaseHas('system_incidents',['id'=>$first->id,'status'=>'investigating']);
        $this->actingAs($admin)->patch(route('admin.monitoring.incidents.status',$first),['status'=>'false_positive','resolution_notes'=>'Controlled test only.'])->assertRedirect(route('admin.monitoring.index'));
        $this->assertDatabaseHas('system_incidents',['id'=>$first->id,'status'=>'false_positive','resolved_by'=>$admin->id]);
    }

    public function test_run_checks_button_and_problem_report_work_without_clinical_content_fields()
    {
        $admin=$this->user('Administrator');
        $this->actingAs($admin)->post(route('admin.monitoring.run'))->assertRedirect(route('admin.monitoring.index'));
        $nurse=$this->user('Nurse');
        $this->actingAs($nurse)->post(route('support.problem.store'),[
            'attempted_action'=>'Call next patient','what_happened'=>'The button did not respond.','resource_reference'=>'Queue D-004','additional_notes'=>'Retried once.','password'=>'must-not-store','soap'=>'must-not-store'
        ])->assertRedirect(route('support.problem.create'));
        $incident=SystemIncident::where('category','user_reported')->latest('id')->firstOrFail();
        $encoded=json_encode($incident->toArray());
        $this->assertStringNotContainsString('must-not-store',$encoded);
        $this->actingAs($this->user('Patient'))->get(route('support.problem.create'))->assertForbidden();
    }
}
