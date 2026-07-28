<?php

namespace App\Services;

use App\ClinicNotification;
use App\ClinicQueue;
use App\Models\ActivityLog;
use App\PatientAccount;
use App\StudentComplaint;
use App\User;

class ClinicNotificationService
{
    const TYPES = [
        'new_patient_complaint','patient_added_to_counter_queue','patient_forwarded_to_consultation',
        'patient_nearly_next','patient_next_in_queue','patient_called','patient_recalled',
        'patient_service_started','patient_service_completed','patient_marked_missed',
        'patient_queue_transferred','consultation_completed','queue_cancelled',
        'patient_temporarily_away','patient_returning','patient_call_acknowledged',
        'consultation_reassigned',
    ];

    public function sendToUser($userId, $type, $title, $message, array $context = [], $event = 'once')
    {
        if (! $userId) return null;
        $related = $context['queue_id'] ?? ($context['complaint_id'] ?? ($context['consultation_id'] ?? 'none'));
        $key = implode(':', [$type,$related,$userId,$event]);
        return ClinicNotification::firstOrCreate(['deduplication_key'=>$key], [
            'user_id'=>$userId,'notification_type'=>$type,'type'=>$this->category($type),
            'role_target'=>$context['role_target'] ?? null,
            'title'=>$title,'message'=>$message,'related_patient_id'=>$context['patient_id'] ?? null,
            'related_complaint_id'=>$context['complaint_id'] ?? null,
            'related_queue_id'=>$context['queue_id'] ?? null,
            'related_consultation_id'=>$context['consultation_id'] ?? null,
            'action_url'=>$context['action_url'] ?? null,'is_read'=>false,'delivered_at'=>now(),
            'priority'=>$context['priority'] ?? 'routine',
            'display_until'=>$context['display_until'] ?? null,
        ]);
    }

    public function sendToRoles(array $roles, $type, $title, $message, array $context = [], $event = 'once')
    {
        return User::where('status','Active')->whereHas('role', function ($query) use ($roles) {
            $query->whereIn('name',$roles);
        })->get()->map(function ($user) use ($type,$title,$message,$context,$event) {
            return $this->sendToUser($user->id,$type,$title,$message,
                array_merge($context,['role_target'=>optional($user->role)->name]),$event);
        });
    }

    public function newComplaint(StudentComplaint $complaint)
    {
        $this->sendToRoles(['Nurse','Staff'],'new_patient_complaint','New Patient Complaint',
            'A new clinic complaint has been submitted by '.$complaint->student_name.'. Category: '.($complaint->complaint_category ?: 'Clinic concern').'.',
            ['complaint_id'=>$complaint->id,'patient_id'=>$complaint->patient_id,
                'action_url'=>route('student-complaints.show',$complaint)]);
        $this->log($complaint->student->user_id,'New complaint notification generated','Complaint #'.$complaint->id.'; nurse/counter staff notified.');
    }

    public function doctorsForwarded(ClinicQueue $queue)
    {
        $forwardedBy=optional($queue->nurse)->fullName();
        $message='Queue '.$queue->ticket_number.' was forwarded for consultation. Priority: '.ucfirst($queue->priority)
            .($forwardedBy?' · Forwarded by: '.$forwardedBy:'').'.';
        $context=['queue_id'=>$queue->id,'complaint_id'=>$queue->student_complaint_id,
            'patient_id'=>optional($queue->complaint)->patient_id,'consultation_id'=>$queue->consultation_id,
            'action_url'=>route('dashboard'),
            'priority'=>in_array($queue->priority, ['urgent','high']) ? 'persistent' : 'routine'];
        if ($queue->assigned_doctor_id) {
            $this->sendToUser($queue->assigned_doctor_id,'patient_forwarded_to_consultation','New Consultation in Queue',$message,$context);
        } else {
            $this->sendToRoles(['Doctor'],'patient_forwarded_to_consultation',
                $queue->priority === 'urgent' || $queue->priority === 'high' ? 'High-Priority Consultation' : 'New Consultation in Queue',
                $message,$context);
        }
        $this->log($queue->assigned_nurse_id,'Doctor notified','Consultation queue #'.$queue->id.' notification generated.');
    }

    public function patientQueueEvent(ClinicQueue $queue, $type, $title, $message, $event = 'once')
    {
        $userId=$this->patientRecipient($queue->account);
        return $this->sendToUser($userId,$type,$title,$message,[
            'queue_id'=>$queue->id,'complaint_id'=>$queue->student_complaint_id,
            'patient_id'=>optional($queue->complaint)->patient_id,'consultation_id'=>$queue->consultation_id,
            'action_url'=>route('student.dashboard'),
            'priority'=>in_array($type, ['patient_next_in_queue','patient_called','patient_recalled'], true)
                ? 'persistent' : 'routine',
        ],$event);
    }

    public function positionNotifications(ClinicQueueService $queueService)
    {
        $date=now()->toDateString();
        $waiting=\App\ClinicQueue::where('queue_date',$date)->where('status','waiting')
            ->orderByRaw("CASE priority WHEN 'urgent' THEN 1 WHEN 'high' THEN 2 WHEN 'moderate' THEN 3 WHEN 'low' THEN 4 ELSE 5 END")
            ->orderBy('created_at')->get();
        $next=$queueService->nextCandidate($date);
        if ($next && ! $next->next_notified_at) {
            $this->patientQueueEvent($next,'patient_next_in_queue',"You're Next in Line",
                'Please return to the clinic and prepare to be called. Queue Number: '.$next->ticket_number);
            $next->update(['next_notified_at'=>now()]);
            $this->log($next->assigned_staff_id,'Patient next notification sent','Queue #'.$next->id.'.');
        }
        $threshold=max(0,config('clinic_queue.nearly_next_threshold',1));
        $eligible=$waiting->reject(function ($entry) use ($next) { return $next && $entry->id===$next->id; })->take($threshold);
        foreach ($eligible as $entry) {
            if (! $entry->nearly_next_notified_at) {
                $this->patientQueueEvent($entry,'patient_nearly_next',"You're Almost Next",
                    'Only one patient is ahead of you. Please return to the clinic or stay nearby.');
                $entry->update(['nearly_next_notified_at'=>now()]);
                $this->log($entry->assigned_staff_id,'Patient nearly-next notification sent','Queue #'.$entry->id.'.');
            }
        }
    }

    public function patientRecipient(PatientAccount $account = null)
    {
        if (! $account) return null;
        return $account->user_id ?: optional($account->sponsor)->user_id;
    }

    public function log($actorId, $action, $description)
    {
        if (! $actorId) return;
        ActivityLog::create(['user_id'=>$actorId,'action'=>$action,'description'=>$description]);
    }

    private function category($type)
    {
        if ($type === 'consultation_completed') return 'consultation_completed';
        if (strpos($type,'consultation') !== false) return 'consultation';
        if (strpos($type,'patient_') === 0 || $type === 'queue_cancelled') return 'queue';
        return 'general';
    }
}
