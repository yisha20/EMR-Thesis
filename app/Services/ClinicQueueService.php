<?php

namespace App\Services;

use App\ClinicNotification;
use App\ClinicQueue;
use App\QueueStatusLog;
use App\StudentComplaint;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\ValidationException;

class ClinicQueueService
{
    public function enqueue(StudentComplaint $complaint, $type, $priority, $actorId, $consultationId = null)
    {
        return DB::transaction(function () use ($complaint, $type, $priority, $actorId, $consultationId) {
            $existing = ClinicQueue::where('student_complaint_id', $complaint->id)->where('queue_type', $type)
                ->whereIn('status', ['waiting','called','serving'])->lockForUpdate()->first();
            if ($existing) return $existing;
            if (! $complaint->patient_account_id && $complaint->student && $complaint->student->user) {
                $account = $complaint->student->user->ensurePatientAccount();
                $complaint->update(['patient_account_id'=>optional($account)->id]);
            }
            abort_unless($complaint->patient_account_id, 422, 'The complaint is not linked to a patient portal account.');
            $date = now()->toDateString();
            DB::table('clinic_queue_sequences')->insertOrIgnore([
                'queue_date'=>$date, 'queue_type'=>$type, 'last_sequence'=>0,
                'created_at'=>now(), 'updated_at'=>now(),
            ]);
            $sequence = DB::table('clinic_queue_sequences')->where('queue_date',$date)
                ->where('queue_type',$type)->lockForUpdate()->first();
            $next = $sequence->last_sequence + 1;
            DB::table('clinic_queue_sequences')->where('id',$sequence->id)
                ->update(['last_sequence'=>$next,'updated_at'=>now()]);
            $prefix = $type === 'counter' ? 'C' : 'D';
            $queue = ClinicQueue::create([
                'queue_date'=>$date,'queue_type'=>$type,'ticket_number'=>$prefix.'-'.str_pad($next,3,'0',STR_PAD_LEFT),
                'patient_account_id'=>$complaint->patient_account_id,'student_complaint_id'=>$complaint->id,
                'consultation_id'=>$consultationId,'priority'=>strtolower($priority),'status'=>'waiting','position'=>$next,
                'assigned_staff_id'=>$actorId,'assigned_nurse_id'=>$actorId,
            ]);
            QueueStatusLog::create(['clinic_queue_id'=>$queue->id,'changed_by'=>$actorId,'to_status'=>'waiting','reason'=>'Added to '.$type.' queue.']);
            $userId = optional($complaint->patientAccount)->user_id;
            if ($userId) ClinicNotification::create([
                'user_id'=>$userId,'related_patient_id'=>$complaint->patient_id,'related_consultation_id'=>$consultationId,
                'related_queue_id'=>$queue->id,'title'=>'Clinic queue update',
                'message'=>$type === 'counter' ? 'Your clinic request has been added to the counter queue.' : 'Your clinic request has been forwarded to the doctor.',
                'type'=>'queue','is_read'=>false,
            ]);
            return $queue;
        });
    }

    public function transition(ClinicQueue $queue, $status, $actorId, $reason = null)
    {
        return DB::transaction(function () use ($queue, $status, $actorId, $reason) {
            $queue = ClinicQueue::whereKey($queue->id)->lockForUpdate()->firstOrFail();
            $from = $queue->status;
            $allowed = [
                'waiting'=>['called','serving','cancelled','missed','transferred'],
                'called'=>['called','serving','missed','cancelled','transferred'],
                'serving'=>['completed','cancelled','transferred'],
            ];
            if (! in_array($status, $allowed[$from] ?? [], true)) {
                throw ValidationException::withMessages(['status'=>'That queue action is not valid from '.$from.'.']);
            }
            $updates = ['status'=>$status,'assigned_staff_id'=>$actorId];
            if ($status === 'called') $updates['called_at'] = now();
            if ($status === 'serving') $updates['serving_started_at'] = now();
            if (in_array($status,['completed','cancelled','transferred'],true)) $updates['completed_at'] = now();
            if ($status === 'missed') $updates['missed_at'] = now();
            $queue->update($updates);
            QueueStatusLog::create(['clinic_queue_id'=>$queue->id,'changed_by'=>$actorId,'from_status'=>$from,'to_status'=>$status,'reason'=>$reason]);
            $messages = ['called'=>'It is your turn. Please proceed to the designated clinic area.','serving'=>'Your clinic service has started.','completed'=>'Your clinic queue service has been completed.','missed'=>'Your queue number was missed. Please contact clinic staff.','cancelled'=>'Your clinic queue request was cancelled.','transferred'=>'You were transferred to another clinic queue.'];
            if (isset($messages[$status]) && optional($queue->account)->user_id) ClinicNotification::create([
                'user_id'=>$queue->account->user_id,'related_queue_id'=>$queue->id,'title'=>'Queue '.$queue->ticket_number,
                'message'=>$messages[$status],'type'=>'queue','is_read'=>false,
            ]);
            return $queue;
        });
    }

    public function nextCandidate($date = null)
    {
        $date = $date ?: now()->toDateString();
        $waiting = ClinicQueue::where('queue_date',$date)->where('status','waiting')
            ->orderByRaw("CASE priority WHEN 'urgent' THEN 1 WHEN 'high' THEN 2 WHEN 'moderate' THEN 3 WHEN 'low' THEN 4 ELSE 5 END")
            ->orderBy('created_at')->get();
        if ($waiting->isEmpty()) return null;
        $best = $waiting->first()->priority;
        $eligible = $waiting->where('priority',$best);
        $state = DB::table('clinic_queue_dispatch_states')->where('queue_date',$date)->first();
        if ($state && $state->policy === 'manual') return null;
        if ($state && $state->policy === 'strict_priority') return $eligible->first();
        $preferred = optional($state)->last_dispatched_type === 'counter' ? 'consultation' : 'counter';
        return $eligible->firstWhere('queue_type',$preferred) ?: $eligible->first();
    }

    public function callNext($actorId)
    {
        return DB::transaction(function () use ($actorId) {
            $date=now()->toDateString();
            DB::table('clinic_queue_dispatch_states')->insertOrIgnore([
                'queue_date'=>$date,'policy'=>'alternating','created_at'=>now(),'updated_at'=>now(),
            ]);
            DB::table('clinic_queue_dispatch_states')->where('queue_date',$date)->lockForUpdate()->first();
            $candidate=$this->nextCandidate($date);
            if (! $candidate) throw ValidationException::withMessages(['queue'=>'There is no automatic next patient.']);
            $candidate=$this->transition($candidate,'called',$actorId,'Called from Nurse Dashboard.');
            DB::table('clinic_queue_dispatch_states')->where('queue_date',$date)->update([
                'last_dispatched_type'=>$candidate->queue_type,'updated_by'=>$actorId,'updated_at'=>now(),
            ]);
            return $candidate;
        });
    }
}
