<?php

namespace App\Services;

use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\Storage;

class ClinicMonitoringService
{
    private $monitor;

    public function __construct(WorkflowMonitor $monitor)
    {
        $this->monitor = $monitor;
    }

    public function run($writeIncidents = true, $criticalOnly = false)
    {
        $checks = [];
        $this->healthChecks($checks);
        $this->dataChecks($checks, $criticalOnly);

        if ($writeIncidents) {
            foreach ($checks as $check) {
                if ($check['count'] > 0 && ! in_array($check['status'], ['healthy', 'not_configured'], true)) {
                    $this->monitor->createIncident([
                        'severity' => $check['severity'],
                        'category' => $check['category'],
                        'event_type' => $check['key'],
                        'deduplication_key' => 'monitor:'.$check['key'],
                        'safe_message' => $check['message'],
                        'technical_message' => 'Count detected: '.$check['count'],
                    ]);
                }
            }
        }

        return ['checked_at' => now(), 'checks' => $checks, 'summary' => $this->summary($checks)];
    }

    public function dailyReport()
    {
        $today = now()->toDateString();
        $report = [
            'date' => $today,
            'environment' => app()->environment(),
            'generated_at' => now()->toIso8601String(),
            'complaints' => $this->datedCount('student_complaints', 'created_at', $today),
            'queue_entries_created' => $this->datedCount('clinic_queues', 'created_at', $today),
            'completed_counter_services' => $this->datedCount('counter_services', 'handled_at', $today),
            'completed_consultations' => $this->whereDateCount('consultations', 'completed_at', $today),
            'failed_actions' => $this->whereDateCount('workflow_action_logs', 'created_at', $today, ['result' => 'failed']),
            'authorization_incidents' => $this->whereDateCount('system_incidents', 'detected_at', $today, ['category' => 'authorization']),
            'open_incidents' => $this->tableCount('system_incidents', [['status', 'in', ['open', 'investigating']]]),
            'resolved_incidents' => $this->whereDateCount('system_incidents', 'resolved_at', $today),
        ];
        $result = $this->run(false);
        foreach (['duplicate_active_queues','duplicate_queue_numbers','stuck_queues','missing_medical_records','failed_notifications'] as $key) {
            $report[$key] = collect($result['checks'])->firstWhere('key', $key)['count'] ?? 0;
        }
        return $report;
    }

    public function storeDailyReport(array $report)
    {
        $path = 'private/monitoring/daily-monitoring-'.$report['date'].'.json';
        Storage::disk('local')->put($path, json_encode($report, JSON_PRETTY_PRINT));
        return storage_path('app/'.$path);
    }

    private function healthChecks(&$checks)
    {
        try { DB::connection()->getPdo(); $this->add($checks,'database','Database',0,'healthy','low','database','Database connection is available.'); }
        catch (\Throwable $e) { $this->add($checks,'database','Database',1,'critical','critical','database','Database connection is unavailable.'); }

        $this->add($checks,'storage','Storage',is_writable(storage_path())?0:1,is_writable(storage_path())?'healthy':'critical','critical','storage',is_writable(storage_path())?'Application storage is writable.':'Application storage is not writable.');
        $this->add($checks,'cache','Bootstrap Cache',is_writable(base_path('bootstrap/cache'))?0:1,is_writable(base_path('bootstrap/cache'))?'healthy':'critical','critical','server',is_writable(base_path('bootstrap/cache'))?'Bootstrap cache is writable.':'Bootstrap cache is not writable.');
        $gd=extension_loaded('gd'); $this->add($checks,'pdf_gd','PDF / GD',$gd?0:1,$gd?'healthy':'warning','medium','pdf',$gd?'PDF image processing is available.':'GD image processing is unavailable.');
        $pdf=class_exists(\Barryvdh\DomPDF\Facade::class) || class_exists(\Dompdf\Dompdf::class); $this->add($checks,'pdf_library','PDF Library',$pdf?0:1,$pdf?'healthy':'critical','high','pdf',$pdf?'PDF generation library is available.':'PDF generation library is unavailable.');
        $this->add($checks,'scheduler','Scheduler',0,app()->environment('local')?'not_configured':'healthy','low','server',app()->environment('local')?'Manual local checks are available.':'Scheduler configuration must be verified by deployment staff.');
        $this->add($checks,'backup','Backup',0,app()->environment('local')?'not_configured':'warning','low','server',app()->environment('local')?'Local backup is not verified.':'Server backup execution must be verified.');
        $free=@disk_free_space(storage_path()); $warning=$free!==false && $free < 1073741824; $this->add($checks,'disk_space','Disk Space',$warning?1:0,$warning?'warning':'healthy','medium','storage',$warning?'Less than 1 GB of disk space remains.':'Disk space is available.');
    }

    private function dataChecks(&$checks, $criticalOnly)
    {
        if (! Schema::hasTable('clinic_queues')) return;
        $active=['waiting','called','serving'];
        $duplicates=DB::table('clinic_queues')->select('student_complaint_id')->whereIn('status',$active)->whereNotNull('student_complaint_id')->groupBy('student_complaint_id')->havingRaw('COUNT(*) > 1')->get()->count();
        $this->add($checks,'duplicate_active_queues','Duplicate Active Queues',$duplicates,$duplicates?'critical':'healthy','critical','queue',$duplicates?'Multiple active queues exist for the same complaint.':'No duplicate active queue entries detected.');
        $numbers=DB::table('clinic_queues')->select('queue_date','queue_type','ticket_number')->groupBy('queue_date','queue_type','ticket_number')->havingRaw('COUNT(*) > 1')->get()->count();
        $this->add($checks,'duplicate_queue_numbers','Duplicate Queue Numbers',$numbers,$numbers?'critical':'healthy','critical','queue',$numbers?'Duplicate queue numbers were detected.':'Queue numbers are unique by date and type.');
        $orphanComplaint=DB::table('clinic_queues as q')->leftJoin('student_complaints as c','c.id','=','q.student_complaint_id')->whereNull('c.id')->count();
        $orphanAccount=DB::table('clinic_queues as q')->leftJoin('patient_accounts as a','a.id','=','q.patient_account_id')->whereNull('a.id')->count();
        $this->add($checks,'orphan_queue_records','Orphan Queue Records',$orphanComplaint+$orphanAccount,($orphanComplaint+$orphanAccount)?'critical':'healthy','high','database',($orphanComplaint+$orphanAccount)?'Queue records have a missing complaint or patient account.':'No orphan queue records detected.');
        $stuck=DB::table('clinic_queues')->where(function($q){$q->where('status','called')->where('called_at','<',now()->subMinutes(20))->orWhere(function($s){$s->where('status','serving')->where('serving_started_at','<',now()->subHours(2));});})->count();
        $this->add($checks,'stuck_queues','Stuck Queues',$stuck,$stuck?'warning':'healthy','medium','queue',$stuck?'Called or serving queues have exceeded the expected duration.':'No stuck queues detected.');
        if ($criticalOnly || ! Schema::hasTable('consultations')) return;
        $missingPatient=DB::table('consultations as c')->leftJoin('patients as p','p.id','=','c.patient_id')->whereNull('p.id')->count();
        $unassigned=DB::table('consultations')->whereNull('doctor_id')->whereIn('status',['Pending Consultation','Called','In Progress'])->count();
        $this->add($checks,'unassigned_consultations','Unassigned Consultations',$unassigned,$unassigned?'warning':'healthy','medium','workflow',$unassigned?'Active consultations do not have an assigned doctor.':'All active consultations have an assigned doctor.');
        $missingRecords=DB::table('consultations as c')->leftJoin('medical_records as m','m.consultation_id','=','c.id')->whereNotNull('c.completed_at')->whereNull('m.id')->count();
        $this->add($checks,'missing_medical_records','Missing Medical Records',$missingRecords,$missingRecords?'critical':'healthy','high','workflow',$missingRecords?'Completed consultations are missing medical records.':'Completed consultations have medical records.');
        $mismatch=DB::table('clinic_queues as q')->join('consultations as c','c.id','=','q.consultation_id')->whereNotNull('q.assigned_doctor_id')->whereNotNull('c.doctor_id')->whereColumn('q.assigned_doctor_id','<>','c.doctor_id')->count();
        $this->add($checks,'doctor_assignment_mismatch','Doctor Assignment Mismatch',$mismatch,$mismatch?'warning':'healthy','medium','workflow',$mismatch?'Queue and consultation doctor assignments do not match.':'Doctor assignments are consistent.');
        $prescriptionOrphans=Schema::hasTable('prescriptions')?DB::table('prescriptions as p')->leftJoin('consultations as c','c.id','=','p.consultation_id')->whereNull('c.id')->count():0;
        $certificateOrphans=Schema::hasTable('medical_certificates')?DB::table('medical_certificates as m')->leftJoin('users as u','u.id','=','m.issued_by_doctor_id')->whereNull('u.id')->count():0;
        $dentalOrphans=Schema::hasTable('dental_referrals')?DB::table('dental_referrals as d')->leftJoin('patients as p','p.id','=','d.patient_id')->whereNull('p.id')->count():0;
        $assessmentOrphans=Schema::hasTable('health_assessments')?DB::table('health_assessments as h')->leftJoin('patients as p','p.id','=','h.patient_id')->whereNull('p.id')->count():0;
        $dependentOrphans=Schema::hasTable('patient_dependents')?DB::table('patient_dependents as d')->leftJoin('patient_accounts as a','a.id','=','d.sponsor_patient_account_id')->whereNull('a.id')->count():0;
        $orphans=$missingPatient+$prescriptionOrphans+$certificateOrphans+$dentalOrphans+$assessmentOrphans+$dependentOrphans;
        $this->add($checks,'orphan_records','Orphan Records',$orphans,$orphans?'critical':'healthy','high','database',$orphans?'Related clinical records have missing parent records.':'No orphan clinical records detected.');
        $failedNotifications=Schema::hasTable('notifications') && Schema::hasColumn('notifications','delivered_at')?DB::table('notifications')->where('priority','persistent')->whereNull('delivered_at')->where('created_at','<',now()->subMinutes(5))->count():0;
        $this->add($checks,'failed_notifications','Failed Notifications',$failedNotifications,$failedNotifications?'warning':'healthy','high','notification',$failedNotifications?'Critical notifications remain undelivered.':'No failed notifications detected.');
        $failedActions=Schema::hasTable('workflow_action_logs')?DB::table('workflow_action_logs')->where('result','failed')->where('created_at','>=',now()->subDay())->count():0;
        $this->add($checks,'failed_actions','Failed Actions',$failedActions,$failedActions?'warning':'healthy','medium','workflow',$failedActions?'Workflow actions failed during the last 24 hours.':'No workflow action failures in the last 24 hours.');
        $denials=Schema::hasTable('system_incidents')?DB::table('system_incidents')->where('category','authorization')->where('detected_at','>=',now()->subDay())->count():0;
        $this->add($checks,'authorization_denials','Unexplained 403s',$denials,$denials?'warning':'healthy','medium','authorization',$denials?'Authorization denials require classification.':'No recent authorization denials detected.');
    }

    private function add(&$checks,$key,$label,$count,$status,$severity,$category,$message){$checks[] = compact('key','label','count','status','severity','category','message');}
    private function summary($checks){return ['healthy'=>collect($checks)->where('status','healthy')->count(),'warning'=>collect($checks)->where('status','warning')->count(),'critical'=>collect($checks)->where('status','critical')->count(),'not_configured'=>collect($checks)->where('status','not_configured')->count()];}
    private function datedCount($table,$column,$date){return Schema::hasTable($table)?DB::table($table)->whereDate($column,$date)->count():0;}
    private function whereDateCount($table,$column,$date,array $where=[]){if(!Schema::hasTable($table))return 0;$q=DB::table($table)->whereDate($column,$date);foreach($where as $k=>$v)$q->where($k,$v);return $q->count();}
    private function tableCount($table,array $conditions){if(!Schema::hasTable($table))return 0;$q=DB::table($table);foreach($conditions as $c){$c[1]==='in'?$q->whereIn($c[0],$c[2]):$q->where($c[0],$c[1],$c[2]);}return $q->count();}
}
