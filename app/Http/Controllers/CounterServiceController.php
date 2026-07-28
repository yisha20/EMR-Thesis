<?php

namespace App\Http\Controllers;

use App\ClinicQueue;
use App\CounterService;
use App\Helpers\ActivityLogger;
use App\MedicalRecord;
use App\Services\ClinicQueueService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class CounterServiceController extends Controller
{
    public function start(Request $request, ClinicQueue $queue, ClinicQueueService $queues)
    {
        DB::transaction(function () use ($request, $queue, $queues) {
            $queue = ClinicQueue::whereKey($queue->id)->lockForUpdate()->firstOrFail();
            abort_unless($queue->queue_type === 'counter', 422, 'Only a counter queue can start counter service.');
            abort_unless(in_array($queue->status, ['waiting', 'called'], true), 422,
                'This counter service cannot be started.');
            $queues->transition($queue, 'serving', $request->user()->id, 'Counter service started.');
            $queue->complaint->update(['status' => 'Counter Service']);
            ActivityLogger::log('started counter service', $queue->ticket_number);
        });

        return redirect()->route('counter-services.show', $queue)
            ->with('success', 'Counter service started.');
    }

    public function show(Request $request, ClinicQueue $queue)
    {
        abort_unless($queue->queue_type === 'counter'
            && in_array($queue->status, ['waiting', 'called', 'serving'], true), 404);
        $queue->load([
            'complaint.patient.healthExaminationRecord', 'complaint.patient.medicalRecords',
            'complaint.complaintOptions', 'account.user',
        ]);
        $availableDoctors = \App\User::with('doctorProfile')->where('status', 'Active')
            ->whereNull('deleted_at')->whereHas('role', function ($query) { $query->where('name', 'Doctor'); })
            ->whereHas('doctorProfile', function ($query) { $query->where('availability', 'available'); })
            ->withCount(['doctorConsultations as waiting_consultations_count' => function ($query) {
                $query->whereIn('status', ['Pending Consultation', 'Called']);
            }])->orderBy('last_name')->get();

        return view('counter-services.show', compact('queue', 'availableDoctors'));
    }

    public function complete(Request $request, ClinicQueue $queue, ClinicQueueService $queues)
    {
        $data = $request->validate([
            'action_type' => 'required|in:counter_remedy,basic_service',
            'service_provided' => 'required|string|max:5000',
            'quantity' => 'nullable|string|max:100',
            'medication_name' => 'nullable|string|max:255',
            'dose' => 'nullable|string|max:255',
            'nursing_intervention' => 'nullable|string|max:5000',
            'notes' => 'nullable|string|max:5000',
            'outcome' => 'required|string|max:255',
        ]);

        DB::transaction(function () use ($request, $queue, $queues, $data) {
            $queue = ClinicQueue::whereKey($queue->id)->lockForUpdate()->firstOrFail();
            abort_unless($queue->queue_type === 'counter' && $queue->status === 'serving', 422);
            $complaint = $queue->complaint;
            abort_unless($complaint && $complaint->patient_id, 422, 'The complaint has no linked patient record.');
            $notes = collect([
                $data['medication_name'] ? 'Medication: '.$data['medication_name'] : null,
                $data['dose'] ? 'Dose: '.$data['dose'] : null,
                $data['nursing_intervention'] ?? null,
                $data['notes'] ?? null,
            ])->filter()->implode("\n");
            $service = CounterService::updateOrCreate(
                ['student_complaint_id' => $complaint->id],
                [
                    'patient_id' => $complaint->patient_id, 'remedy_given' => $data['service_provided'],
                    'quantity' => $data['quantity'] ?? null, 'notes' => $notes ?: null,
                    'handled_by' => $request->user()->id, 'outcome' => $data['outcome'], 'handled_at' => now(),
                ]
            );
            $record = MedicalRecord::updateOrCreate(
                ['student_complaint_id' => $complaint->id],
                [
                    'patient_id' => $complaint->patient_id, 'counter_service_id' => $service->id,
                    'record_type' => 'Counter Service', 'source' => 'Digital Intake',
                    'description' => $data['service_provided'], 'outcome' => $data['outcome'],
                    'consultation_status' => 'Counter Resolved', 'chief_complaint' => $complaint->chief_complaint,
                    'diagnosis' => 'Counter intervention', 'recommendation' => $notes ?: null,
                    'date_of_consultation' => now()->toDateString(), 'time_of_consultation' => now()->format('H:i:s'),
                    'attending_staff_id' => $request->user()->id, 'created_by' => $request->user()->id,
                ]
            );
            $complaint->update(['medical_record_id' => $record->id]);
            $queues->transition($queue, 'completed', $request->user()->id, 'Counter service completed.');
            ActivityLogger::log('completed counter service', $queue->ticket_number);
        });

        return redirect()->route('dashboard')->with('success', 'Counter service completed.');
    }
}
