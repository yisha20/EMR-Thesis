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
    public function enqueue(StudentComplaint $complaint, $type, $priority, $actorId, $consultationId = null, $doctorId = null)
    {
        return DB::transaction(function () use ($complaint, $type, $priority, $actorId, $consultationId, $doctorId) {
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
                'assigned_staff_id'=>$actorId,'assigned_nurse_id'=>$actorId,'assigned_doctor_id'=>$doctorId,
            ]);
            QueueStatusLog::create(['clinic_queue_id'=>$queue->id,'changed_by'=>$actorId,'to_status'=>'waiting','reason'=>'Added to '.$type.' queue.']);
            $notifier=app(ClinicNotificationService::class);
            $ahead=ClinicQueue::where('queue_date',$date)->where('queue_type',$type)
                ->where('status','waiting')->where('position','<',$next)->count();
            $notifier->patientQueueEvent($queue,
                $type === 'counter' ? 'patient_added_to_counter_queue' : 'patient_forwarded_to_consultation',
                $type === 'counter' ? 'Added to Counter Service Queue' : 'Added to Doctor Consultation Queue',
                'Queue Number: '.$queue->ticket_number.'. Patients Ahead: '.$ahead.'.');
            if ($type === 'consultation') $notifier->doctorsForwarded($queue);
            $notifier->log($actorId,'Patient added to queue','Queue #'.$queue->id.' created.');
            $notifier->positionNotifications($this);
            return $queue;
        });
    }

    public function transition(ClinicQueue $queue, $status, $actorId, $reason = null)
    {
        return DB::transaction(function () use ($queue, $status, $actorId, $reason) {
            $queue = ClinicQueue::whereKey($queue->id)->lockForUpdate()->firstOrFail();
            $from = $queue->status;
            $allowed = [
                'waiting'=>['called','serving','cancelled','transferred'],
                'called'=>['called','serving','missed','cancelled','transferred'],
                'serving'=>['completed','cancelled','transferred'],
            ];
            if (! in_array($status, $allowed[$from] ?? [], true)) {
                throw ValidationException::withMessages(['status'=>'That queue action is not valid from '.$from.'.']);
            }
            $isRecall=$from === 'called' && $status === 'called';
            $grace=config('clinic_queue.call_grace_minutes',5);
            if ($isRecall) {
                if ($queue->recall_count >= config('clinic_queue.max_recalls',2)) {
                    throw ValidationException::withMessages(['status'=>'The maximum recall count has been reached.']);
                }
                $since=$queue->last_recalled_at ?: $queue->called_at;
                if ($since && now()->lt($since->copy()->addMinutes($grace))) {
                    throw ValidationException::withMessages(['status'=>'Wait for the '.$grace.'-minute response grace period before recalling.']);
                }
            }
            if ($status === 'missed') {
                $since=$queue->last_recalled_at ?: $queue->called_at;
                if ($queue->recall_count < config('clinic_queue.max_recalls',2)) {
                    throw ValidationException::withMessages(['status'=>'Use the allowed recalls before marking this patient missed.']);
                }
                if ($since && now()->lt($since->copy()->addMinutes($grace))) {
                    throw ValidationException::withMessages(['status'=>'The response grace period has not elapsed.']);
                }
            }
            $updates = ['status'=>$status,'assigned_staff_id'=>$actorId];
            if ($status === 'called' && ! $isRecall) $updates['called_at'] = now();
            if ($isRecall) {
                $updates['recall_count']=$queue->recall_count+1;
                $updates['last_recalled_at']=now();
            }
            if ($status === 'serving') $updates['serving_started_at'] = now();
            if (in_array($status,['completed','cancelled','transferred'],true)) $updates['completed_at'] = now();
            if ($status === 'missed') {
                $updates['missed_at'] = now();
                $updates['missed_reason']=$reason;
            }
            $queue->update($updates);
            if ($queue->queue_type==='consultation' && $queue->consultation) {
                $consultationUpdates=[];
                if ($status==='called') $consultationUpdates=['status'=>'Called','called_at'=>$queue->called_at ?: now(),'called_by'=>$actorId];
                if ($status==='serving') $consultationUpdates=['status'=>'In Consultation','started_at'=>now()];
                if ($status==='completed') $consultationUpdates=['status'=>'Completed','completed_at'=>now()];
                if ($status==='missed') $consultationUpdates=['status'=>'Missed'];
                if ($status==='cancelled') $consultationUpdates=['status'=>'Cancelled'];
                if ($status==='transferred') $consultationUpdates=['status'=>'Cancelled'];
                if ($consultationUpdates) $queue->consultation->update($consultationUpdates);
            }
            if ($status==='completed') {
                $queue->complaint()->update(['status'=>$queue->queue_type==='counter'?'Counter Resolved':'Completed','completed_at'=>now()]);
            }
            QueueStatusLog::create(['clinic_queue_id'=>$queue->id,'changed_by'=>$actorId,'from_status'=>$from,'to_status'=>$status,'reason'=>$reason]);
            $notifier=app(ClinicNotificationService::class);
            $events=[
                'serving'=>['patient_service_started','Service Started','Your clinic service has started. Status: In Service.'],
                'completed'=>['patient_service_completed','Service Completed','Your clinic service has been completed. You may view the updated record in Health History.'],
                'missed'=>['patient_marked_missed','Queue Call Missed','Your queue number was marked as missed. Please contact clinic staff if you still need assistance.'],
                'cancelled'=>['queue_cancelled','Queue Cancelled','Your clinic queue request was cancelled.'],
                'transferred'=>['patient_queue_transferred','Queue Transferred','You were transferred to another clinic queue.'],
            ];
            if ($status === 'called') {
                $notifier->patientQueueEvent($queue,$isRecall?'patient_recalled':'patient_called',
                    $isRecall?'Recall Notice':"It's Your Turn",
                    'Queue Number '.$queue->ticket_number.($isRecall?' is being called again.':' is now being called.').' Please proceed immediately to the clinic.',
                    $isRecall?'recall-'.$queue->recall_count:'initial');
                if (! $isRecall) $queue->update(['called_notification_sent_at'=>now()]);
                if ($queue->queue_type==='consultation') {
                    $notifier->sendToRoles(['Doctor'],'patient_called','Consultation Patient Ready',
                        'Queue '.$queue->ticket_number.' has been called and is ready for consultation.',
                        ['queue_id'=>$queue->id,'consultation_id'=>$queue->consultation_id,'action_url'=>route('dashboard')],
                        $isRecall?'recall-'.$queue->recall_count:'initial');
                }
            } elseif (isset($events[$status])) {
                $event=$events[$status];
                if ($status==='completed' && $queue->queue_type==='consultation') {
                    $event=['consultation_completed','Consultation Completed',
                        'Your consultation is complete. Check My Prescriptions and Health History for available updates.'];
                }
                $notifier->patientQueueEvent($queue,$event[0],$event[1],$event[2]);
            }
            $notifier->log($actorId,$isRecall?'Patient recalled':ucfirst(str_replace('_',' ','patient '.$status)),
                'Queue #'.$queue->id.' changed from '.$from.' to '.$status.'.');
            $notifier->positionNotifications($this);
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
            if (ClinicQueue::where('queue_date',$date)->where('status','called')->lockForUpdate()->exists()) {
                throw ValidationException::withMessages(['queue'=>'A patient is already being called. Start, miss, or cancel that call before calling another patient.']);
            }
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
