<?php

namespace App\Http\Controllers;

use App\ClinicQueue;
use App\StudentComplaint;
use App\Services\ClinicQueueService;
use Illuminate\Http\Request;
use App\Consultation;
use App\Models\ActivityLog;
use Illuminate\Support\Facades\DB;
use App\Services\ClinicNotificationService;
use App\User;
use Illuminate\Validation\ValidationException;

class ClinicQueueController extends Controller
{
    public function store(Request $request, StudentComplaint $complaint, ClinicQueueService $service)
    {
        $data=$request->validate([
            'queue_type'=>'required|in:counter,consultation',
            'priority'=>'required|in:low,moderate,high,urgent',
            'assigned_doctor_id'=>'required_if:queue_type,consultation|nullable|integer|exists:users,id',
        ]);
        abort_unless($complaint->status === 'Reviewed' || $complaint->queues()->whereIn('status',['waiting','called','serving'])->exists(), 422, 'The complaint must be reviewed before queue routing.');
        $queue=DB::transaction(function()use($request,$complaint,$service,$data){
            $complaint=StudentComplaint::whereKey($complaint->id)->lockForUpdate()->firstOrFail();
            $consultation=$complaint->consultation;
            $doctor = null;
            if ($data['queue_type'] === 'consultation') {
                $doctor = User::whereKey($data['assigned_doctor_id'])->where('status', 'Active')
                    ->whereNull('deleted_at')
                    ->whereHas('role', function ($query) { $query->where('name', 'Doctor'); })
                    ->whereHas('doctorProfile', function ($query) { $query->where('availability', 'available'); })
                    ->lockForUpdate()->first();
                if (! $doctor) {
                    throw ValidationException::withMessages([
                        'assigned_doctor_id' => 'The selected doctor is no longer available. Please select another doctor.',
                    ]);
                }
            }
            if($data['queue_type']==='consultation'&&!$consultation){
                abort_unless($complaint->patient_id,422,'Link the patient record before forwarding to consultation.');
                $consultation=Consultation::create([
                    'student_complaint_id'=>$complaint->id,'patient_id'=>$complaint->patient_id,
                    'service_needed'=>'Medical Consultation','priority'=>ucfirst($data['priority']),
                    'forwarded_by'=>$request->user()->id,'forwarded_at'=>now(),'status'=>'Pending Consultation',
                    'doctor_id'=>$doctor->id,
                ]);
                $complaint->status='Forwarded';
            } elseif ($data['queue_type'] === 'consultation') {
                abort_unless(in_array($consultation->status, ['Pending Consultation', 'Called'], true), 422);
                if ($consultation->doctor_id && (int) $consultation->doctor_id !== (int) $doctor->id) {
                    throw ValidationException::withMessages([
                        'assigned_doctor_id' => 'This consultation is already assigned. Use the audited reassignment action.',
                    ]);
                }
                $consultation->update(['doctor_id' => $doctor->id, 'priority' => ucfirst($data['priority'])]);
            }
            $complaint->triage_priority=$data['priority'];$complaint->save();
            $queue=$service->enqueue($complaint,$data['queue_type'],$data['priority'],$request->user()->id,optional($consultation)->id,optional($doctor)->id);
            if ($doctor && (int) $queue->assigned_doctor_id !== (int) $doctor->id) {
                $queue->update(['assigned_doctor_id' => $doctor->id, 'priority' => $data['priority']]);
            }
            ActivityLog::create(['user_id'=>$request->user()->id,'action'=>$data['queue_type']==='counter'?'Added to counter queue':'Added to consultation queue','description'=>'Complaint #'.$complaint->id.' assigned queue number '.$queue->ticket_number.'.']);
            return $queue;
        });
        $message=$data['queue_type']==='counter'
            ? 'Patient added to the Counter Queue as '.$queue->ticket_number.'.'
            : 'Patient forwarded to the Consultation Queue as '.$queue->ticket_number.'.';
        return redirect()->back()->with('success',$message);
    }

    public function update(Request $request, ClinicQueue $queue, ClinicQueueService $service)
    {
        $data=$request->validate(['status'=>'required|in:called,serving,completed,cancelled,missed,transferred','reason'=>'nullable|string|max:1000']);
        if (in_array($data['status'],['cancelled','transferred','missed'],true)) $request->validate(['reason'=>'required|string|max:1000']);
        $queue=$service->transition($queue,$data['status'],$request->user()->id,$data['reason']??null);
        return redirect()->back()->with('success','Queue status updated.');
    }

    public function presence(Request $request, ClinicQueue $queue, ClinicNotificationService $notifier)
    {
        $data=$request->validate(['presence_status'=>'required|in:waiting_inside,temporarily_away,returning,present']);
        $account=$request->user()->patientAccount;
        abort_unless($account && $account->accessibleAccountIds()->contains($queue->patient_account_id),403);
        abort_unless(in_array($queue->status,['waiting','called'],true),422,'Presence can no longer be changed for this queue entry.');
        DB::transaction(function () use ($queue,$data,$request,$notifier) {
            $queue=ClinicQueue::whereKey($queue->id)->lockForUpdate()->firstOrFail();
            $updates=['presence_status'=>$data['presence_status']];
            if ($data['presence_status']==='temporarily_away') $updates['away_at']=now();
            if ($data['presence_status']==='returning') $updates['returning_at']=now();
            if (in_array($data['presence_status'],['waiting_inside','present'],true)) $updates['present_at']=now();
            $queue->update($updates);
            if ($data['presence_status']==='temporarily_away') {
                $notifier->sendToRoles(['Nurse','Staff'],'patient_temporarily_away','Patient Temporarily Away',
                    'Queue '.$queue->ticket_number.' is waiting outside temporarily.',
                    ['queue_id'=>$queue->id,'complaint_id'=>$queue->student_complaint_id,'action_url'=>route('dashboard')]);
                $notifier->log($request->user()->id,'Patient marked temporarily away','Queue #'.$queue->id.'.');
            } elseif ($data['presence_status']==='returning') {
                $notifier->sendToRoles(['Nurse','Staff'],'patient_returning','Patient Returning',
                    'Queue '.$queue->ticket_number.' is returning to the clinic.',
                    ['queue_id'=>$queue->id,'complaint_id'=>$queue->student_complaint_id,'action_url'=>route('dashboard')],
                    'return-'.$queue->returning_at->timestamp);
                $notifier->log($request->user()->id,'Patient returning','Queue #'.$queue->id.'.');
            }
        });
        return $request->expectsJson()?response()->json(['ok'=>true,'presence_status'=>$data['presence_status']])
            :redirect()->back()->with('success','Queue presence updated. Your queue position was preserved.');
    }

    public function acknowledge(Request $request, ClinicQueue $queue, ClinicNotificationService $notifier)
    {
        $account=$request->user()->patientAccount;
        abort_unless($account && $account->accessibleAccountIds()->contains($queue->patient_account_id),403);
        abort_unless($queue->status==='called',422,'Only an active queue call can be acknowledged.');
        ClinicQueue::whereKey($queue->id)->whereNull('patient_acknowledged_at')->update([
            'patient_acknowledged_at'=>now(),'presence_status'=>'present','present_at'=>now(),
        ]);
        $notifier->log($request->user()->id,'Patient acknowledged call','Queue #'.$queue->id.'.');
        return $request->expectsJson()?response()->json(['ok'=>true]):redirect()->back()->with('success','Call acknowledged.');
    }

    public function transfer(Request $request, ClinicQueue $queue, ClinicQueueService $service)
    {
        $data=$request->validate([
            'queue_type'=>'required|in:counter,consultation',
            'reason'=>'required|string|max:1000',
            'assigned_doctor_id'=>'required_if:queue_type,consultation|nullable|integer|exists:users,id',
        ]);
        abort_if($data['queue_type']===$queue->queue_type,422,'Select a different destination queue.');
        $new=DB::transaction(function() use ($queue,$data,$request,$service) {
            $queue=ClinicQueue::whereKey($queue->id)->lockForUpdate()->firstOrFail();
            abort_unless(in_array($queue->status,['waiting','called'],true),422,'This queue entry cannot be transferred.');
            $complaint=StudentComplaint::whereKey($queue->student_complaint_id)->lockForUpdate()->firstOrFail();
            $consultation=$complaint->consultation;
            $doctor = null;
            if ($data['queue_type'] === 'consultation') {
                $doctor = User::whereKey($data['assigned_doctor_id'])->where('status', 'Active')
                    ->whereNull('deleted_at')
                    ->whereHas('role', function ($query) { $query->where('name', 'Doctor'); })
                    ->whereHas('doctorProfile', function ($query) { $query->where('availability', 'available'); })
                    ->lockForUpdate()->first();
                if (! $doctor) {
                    throw ValidationException::withMessages([
                        'assigned_doctor_id' => 'The selected doctor is no longer available. Please select another doctor.',
                    ]);
                }
            }
            if ($data['queue_type']==='consultation' && ! $consultation) {
                abort_unless($complaint->patient_id,422,'Link the patient record before consultation transfer.');
                $consultation=Consultation::create(['student_complaint_id'=>$complaint->id,'patient_id'=>$complaint->patient_id,
                    'service_needed'=>'Medical Consultation','priority'=>ucfirst($queue->priority),
                    'forwarded_by'=>$request->user()->id,'forwarded_at'=>now(),'status'=>'Pending Consultation',
                    'doctor_id'=>$doctor->id]);
                $complaint->update(['status'=>'Forwarded']);
            } elseif ($doctor) {
                $consultation->update(['doctor_id' => $doctor->id, 'status' => 'Pending Consultation']);
            }
            $service->transition($queue,'transferred',$request->user()->id,$data['reason']);
            $new=$service->enqueue($complaint,$data['queue_type'],$queue->priority,$request->user()->id,
                $data['queue_type']==='consultation'?optional($consultation)->id:null, optional($doctor)->id);
            $new->update(['transferred_from_queue_id'=>$queue->id]);
            return $new;
        });
        return redirect()->back()->with('success','Patient transferred to '.$new->ticket_number.'.');
    }

    public function requeue(Request $request, ClinicQueue $queue, ClinicQueueService $service)
    {
        abort_unless($queue->status==='missed',422,'Only missed entries may return to queue.');
        $new=$service->enqueue($queue->complaint,$queue->queue_type,$queue->priority,$request->user()->id,$queue->consultation_id);
        $new->update(['transferred_from_queue_id'=>$queue->id]);
        return redirect()->back()->with('success','Patient returned to queue as '.$new->ticket_number.'.');
    }

    public function callNext(Request $request, ClinicQueueService $service)
    {
        $queue=$service->callNext($request->user()->id);
        return redirect()->back()->with('success',$queue->ticket_number.' is now being called.');
    }

    public function policy(Request $request)
    {
        $data=$request->validate(['policy'=>'required|in:alternating,strict_priority,manual']);
        DB::table('clinic_queue_dispatch_states')->updateOrInsert(['queue_date'=>now()->toDateString()],[
            'policy'=>$data['policy'],'updated_by'=>$request->user()->id,'updated_at'=>now(),'created_at'=>now(),
        ]);
        return redirect()->back()->with('success','Queue dispatch policy updated.');
    }

    public function live(Request $request)
    {
        $doctor=optional($request->user()->role)->name==='Doctor';
        $entries=ClinicQueue::where('queue_date',now()->toDateString())
            ->whereIn('status',['waiting','called','serving','missed'])
            ->when($doctor,function($query){$query->where('queue_type','consultation');})
            ->orderBy('id')->get(['id','status','presence_status','updated_at'])->map(function($entry){
                return ['id'=>$entry->id,'status'=>$entry->status,'presence_status'=>$entry->presence_status,
                    'presence_label'=>ucwords(str_replace('_',' ',$entry->presence_status)),
                    'updated_at'=>$entry->updated_at->toIso8601String()];
            });
        return response()->json(['entries'=>$entries])->header('Cache-Control','no-store');
    }

    public function status(Request $request)
    {
        $account=$request->user()->patientAccount; abort_unless($account,403);
        $queue=ClinicQueue::whereIn('patient_account_id',$account->accessibleAccountIds())
            ->whereIn('status',['waiting','called','serving'])->latest('id')->first();
        if(!$queue)return response()->json(['queue'=>null])->header('Cache-Control','no-store, no-cache, must-revalidate');
        $rank=['urgent'=>1,'high'=>2,'moderate'=>3,'low'=>4,'unassigned'=>5];
        $ahead=ClinicQueue::where('queue_date',$queue->queue_date)->where('queue_type',$queue->queue_type)->where('status','waiting')
            ->get()->filter(function($q)use($queue,$rank){return $rank[$q->priority]<$rank[$queue->priority]||($q->priority===$queue->priority&&$q->position<$queue->position);})->count();
        $now=ClinicQueue::where('queue_date',$queue->queue_date)->where('queue_type',$queue->queue_type)->whereIn('status',['called','serving'])->orderByDesc('called_at')->value('ticket_number');
        app(ClinicNotificationService::class)->positionNotifications(app(ClinicQueueService::class));
        return response()->json(['queue'=>['id'=>$queue->id,'ticket'=>$queue->ticket_number,'type'=>$queue->queue_type,
            'status'=>$queue->status,'presence_status'=>$queue->presence_status,'patients_ahead'=>$ahead,
            'now_serving'=>$now,'is_nearly_next'=>$ahead===1,'is_next'=>$ahead===0,
            'acknowledged'=>(bool)$queue->patient_acknowledged_at,'updated_at'=>$queue->updated_at->toIso8601String()]])
            ->header('Cache-Control','no-store, no-cache, must-revalidate');
    }
}
