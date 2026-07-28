@extends('layouts.app')

@section('content')
@php($complaint=$queue->complaint)
@php($patient=$complaint->patient)
<div class="dashboard-wrap counter-service-workspace">
    <div class="dashboard-heading"><p class="eyebrow">Counter service workspace</p><h1>{{ $complaint->student_name }}</h1><span>Queue {{ $queue->ticket_number }} · {{ ucfirst(optional($queue->account)->patient_type ?: 'patient') }} · {{ $complaint->student_id_number }}</span><a href="{{ route('dashboard') }}" class="btn btn-light mt-2">Back to Nurse Queue</a></div>
    @if(session('success'))<div class="alert alert-success">{{ session('success') }}</div>@endif
    <section class="dashboard-panel">
        <div class="complaint-detail-grid">
            <div><span>Priority</span><strong>{{ $complaint->triage_priority_label }}</strong></div><div><span>Status</span><strong>{{ $complaint->status }}</strong></div>
            <div class="complaint-detail-wide"><span>Chief Complaint</span><strong>{{ $complaint->chief_complaint }}</strong></div>
            <div class="complaint-detail-wide"><span>Selected Concerns</span><p>{{ $complaint->complaintOptions->pluck('name')->implode(', ') ?: 'None selected' }}</p></div>
            <div class="complaint-detail-wide"><span>Symptoms</span><p>{{ $complaint->symptoms_description ?: 'None provided' }}</p></div>
        </div>
    </section>
    <section class="dashboard-panel mt-3">
        <h2>What action will be provided?</h2>
        <div class="form-group"><label for="counter_action">Action</label><select id="counter_action" class="form-control" data-counter-action><option value="counter_remedy">Counter Remedy</option><option value="basic_service">Basic Clinic Service</option><option value="forward_doctor">Forward to Doctor Consultation</option></select></div>
        <form method="POST" action="{{ route('counter-services.complete', $queue) }}" class="workflow-form workflow-form-grid" data-counter-form="service">
            @csrf <input type="hidden" name="action_type" value="counter_remedy" data-action-type>
            <div class="form-group form-group-wide"><label for="service_provided">Remedy / Service Provided</label><textarea id="service_provided" name="service_provided" class="form-control" required></textarea></div>
            <div class="form-group"><label for="quantity">Quantity</label><input id="quantity" name="quantity" class="form-control"></div>
            <div class="form-group" data-remedy-field><label for="medication_name">Medication Name</label><input id="medication_name" name="medication_name" class="form-control"></div>
            <div class="form-group" data-remedy-field><label for="dose">Dose</label><input id="dose" name="dose" class="form-control"></div>
            <div class="form-group form-group-wide"><label for="nursing_intervention">Nursing Intervention / Vital Signs</label><textarea id="nursing_intervention" name="nursing_intervention" class="form-control"></textarea></div>
            <div class="form-group"><label for="outcome">Outcome</label><input id="outcome" name="outcome" class="form-control" value="Resolved" required></div>
            <div class="form-group form-group-wide"><label for="notes">Notes</label><textarea id="notes" name="notes" class="form-control"></textarea></div>
            <button class="btn btn-primary" data-submit-loading="Completing...">Complete Counter Service</button>
        </form>
        <form method="POST" action="{{ route('clinic-queues.transfer', $queue) }}" class="workflow-form workflow-form-grid" data-counter-form="forward" hidden>
            @csrf <input type="hidden" name="queue_type" value="consultation"><input type="hidden" name="reason" value="Transferred from counter service workspace">
            <div class="form-group"><label for="transfer_doctor">Assigned Doctor</label><select id="transfer_doctor" name="assigned_doctor_id" class="form-control" required>@foreach($availableDoctors as $doctor)<option value="{{ $doctor->id }}">Dr. {{ $doctor->fullName() }} — {{ $doctor->waiting_consultations_count }} waiting</option>@endforeach</select></div>
            <button class="btn btn-primary" data-submit-loading="Routing...">Transfer to Doctor Consultation</button>
        </form>
    </section>
</div>
@endsection

@push('js')
<script>(function(){var select=document.querySelector('[data-counter-action]'),service=document.querySelector('[data-counter-form="service"]'),forward=document.querySelector('[data-counter-form="forward"]'),type=document.querySelector('[data-action-type]');if(!select)return;select.addEventListener('change',function(){var isForward=select.value==='forward_doctor';service.hidden=isForward;forward.hidden=!isForward;if(!isForward)type.value=select.value;document.querySelectorAll('[data-remedy-field]').forEach(function(field){field.hidden=select.value==='basic_service';});});})();</script>
@endpush
