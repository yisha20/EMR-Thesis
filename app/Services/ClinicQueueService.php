<?php

namespace App\Services;

use App\ClinicNotification;
use App\ClinicQueue;
use App\QueueStatusLog;
use App\StudentComplaint;
use Illuminate\Support\Facades\DB;

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
            $last = ClinicQueue::where('queue_date', $date)->where('queue_type', $type)->lockForUpdate()->max('position') ?: 0;
            $prefix = $type === 'counter' ? 'C' : 'D';
            $queue = ClinicQueue::create([
                'queue_date'=>$date,'queue_type'=>$type,'ticket_number'=>$prefix.'-'.str_pad($last+1,3,'0',STR_PAD_LEFT),
                'patient_account_id'=>$complaint->patient_account_id,'student_complaint_id'=>$complaint->id,
                'consultation_id'=>$consultationId,'priority'=>strtolower($priority),'status'=>'waiting','position'=>$last+1,
                'assigned_staff_id'=>$actorId,
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
            $updates = ['status'=>$status,'assigned_staff_id'=>$actorId];
            if ($status === 'called') $updates['called_at'] = now();
            if ($status === 'serving') $updates['serving_started_at'] = now();
            if (in_array($status,['completed','cancelled','missed','transferred'],true)) $updates['completed_at'] = now();
            $queue->update($updates);
            QueueStatusLog::create(['clinic_queue_id'=>$queue->id,'changed_by'=>$actorId,'from_status'=>$from,'to_status'=>$status,'reason'=>$reason]);
            $messages = ['called'=>'It is your turn. Please proceed to the designated clinic area.','completed'=>'Your clinic queue service has been completed.','missed'=>'Your queue number was missed. Please contact clinic staff.'];
            if (isset($messages[$status]) && optional($queue->account)->user_id) ClinicNotification::create([
                'user_id'=>$queue->account->user_id,'related_queue_id'=>$queue->id,'title'=>'Queue '.$queue->ticket_number,
                'message'=>$messages[$status],'type'=>'queue','is_read'=>false,
            ]);
            return $queue;
        });
    }
}
