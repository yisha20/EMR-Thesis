<?php

namespace App\Http\Controllers;

use App\Consultation;
use App\Helpers\ActivityLogger;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use App\ClinicQueue;
use App\Services\ClinicNotificationService;

class ConsultationQueueController extends Controller
{
    public function start(Request $request, Consultation $consultation)
    {
        $complaint = DB::transaction(function () use ($request, $consultation) {
            $consultation = Consultation::whereKey($consultation->id)->lockForUpdate()->firstOrFail();
            abort_unless((int) $consultation->doctor_id === (int) $request->user()->id, 403);
            abort_unless(in_array($consultation->status, ['Pending Consultation', 'Called'], true), 422,
                'This consultation cannot be started.');
            $queue = ClinicQueue::where('consultation_id', $consultation->id)
                ->whereIn('status', ['waiting', 'called'])->lockForUpdate()->firstOrFail();
            abort_unless((int) $queue->assigned_doctor_id === (int) $request->user()->id, 403);
            $startedAt = now();
            $consultation->update(['status' => 'In Consultation', 'started_at' => $startedAt]);
            $queue->update([
                'status' => 'serving', 'serving_started_at' => $startedAt,
                'assigned_staff_id' => $request->user()->id,
            ]);
            $consultation->complaint->update([
                'status' => 'In Consultation', 'consultation_started_at' => $startedAt,
            ]);
            optional($consultation->medicalRecord)->update([
                'consultation_status' => 'In Consultation', 'outcome' => 'In Consultation',
                'attending_staff_id' => $request->user()->id,
                'attending_physician' => $request->user()->fullName(),
            ]);
            optional($request->user()->doctorProfile)->update(['availability' => 'busy']);
            $notifier = app(ClinicNotificationService::class);
            $notifier->patientQueueEvent($queue, 'patient_service_started', 'Consultation Started',
                'Your doctor consultation has started.');
            $notifier->sendToRoles(['Nurse','Staff'], 'consultation_started', 'Consultation Started',
                'Dr. '.$request->user()->fullName().' started consultation for '.$queue->ticket_number.'.',
                ['queue_id'=>$queue->id, 'consultation_id'=>$consultation->id, 'action_url'=>route('dashboard')]);
            ActivityLogger::log('started assigned consultation', $queue->ticket_number);
            return $consultation->complaint;
        });

        return redirect()->route('student-complaints.show', $complaint)
            ->with('success', 'Consultation started for '.$complaint->student_name.'.');
    }

    public function callStudent(Request $request, Consultation $consultation)
    {
        DB::transaction(function () use ($request, $consultation) {
            $consultation = Consultation::whereKey($consultation->id)->lockForUpdate()->firstOrFail();
            abort_unless($consultation->status === 'Pending Consultation', 422, 'This student is no longer waiting to be called.');
            $consultation->update([
                'status' => 'Called',
                'called_at' => now(),
                'called_by' => $request->user()->id,
            ]);
            optional($consultation->medicalRecord)->update(['consultation_status' => 'Called', 'outcome' => 'Called']);
            ActivityLogger::log('called ' . $consultation->complaint->student_name . ' for consultation');
        });

        return redirect()->back()->with('success', 'Student marked as called for consultation.');
    }
}
