<?php

namespace App\Http\Controllers;

use App\ClinicQueue;
use App\StudentComplaint;
use App\Services\ClinicQueueService;
use Illuminate\Http\Request;
use App\Consultation;
use App\Models\ActivityLog;
use Illuminate\Support\Facades\DB;

class ClinicQueueController extends Controller
{
    public function store(Request $request, StudentComplaint $complaint, ClinicQueueService $service)
    {
        $data=$request->validate(['queue_type'=>'required|in:counter,consultation','priority'=>'required|in:low,moderate,high,urgent']);
        abort_unless($complaint->status === 'Reviewed' || $complaint->queues()->whereIn('status',['waiting','called','serving'])->exists(), 422, 'The complaint must be reviewed before queue routing.');
        $queue=DB::transaction(function()use($request,$complaint,$service,$data){
            $complaint=StudentComplaint::whereKey($complaint->id)->lockForUpdate()->firstOrFail();
            $consultation=$complaint->consultation;
            if($data['queue_type']==='consultation'&&!$consultation){
                abort_unless($complaint->patient_id,422,'Link the patient record before forwarding to consultation.');
                $consultation=Consultation::create([
                    'student_complaint_id'=>$complaint->id,'patient_id'=>$complaint->patient_id,
                    'service_needed'=>'Medical Consultation','priority'=>ucfirst($data['priority']),
                    'forwarded_by'=>$request->user()->id,'forwarded_at'=>now(),'status'=>'Pending Consultation',
                ]);
                $complaint->status='Forwarded';
            }
            $complaint->triage_priority=$data['priority'];$complaint->save();
            $queue=$service->enqueue($complaint,$data['queue_type'],$data['priority'],$request->user()->id,optional($consultation)->id);
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
        if (in_array($data['status'],['cancelled','transferred'],true)) $request->validate(['reason'=>'required|string|max:1000']);
        $service->transition($queue,$data['status'],$request->user()->id,$data['reason']??null);
        return redirect()->back()->with('success','Queue status updated.');
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
        return response()->json(['queue'=>['ticket'=>$queue->ticket_number,'type'=>$queue->queue_type,'status'=>$queue->status,'patients_ahead'=>$ahead,'now_serving'=>$now,'updated_at'=>$queue->updated_at->toIso8601String()]])
            ->header('Cache-Control','no-store, no-cache, must-revalidate');
    }
}
