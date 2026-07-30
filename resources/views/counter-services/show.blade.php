@extends('layouts.app')

@section('content')
@php($complaint=$queue->complaint)
@php($patient=$complaint->patient)
<div class="dashboard-wrap counter-service-workspace">
    <header class="counter-service-header">
        <div>
            <p class="eyebrow">Counter service workspace</p>
            <h1>{{ $complaint->student_name }}</h1>
            <p class="counter-service-meta">Queue {{ $queue->ticket_number }} <span aria-hidden="true">·</span> {{ ucfirst(optional($queue->account)->patient_type ?: 'patient') }} <span aria-hidden="true">·</span> {{ $complaint->student_id_number }}</p>
        </div>
        <a href="{{ route('dashboard') }}" class="btn btn-light"><i class="fa fa-arrow-left"></i> Back to Nurse Queue</a>
    </header>
    @if(session('success'))<div class="alert alert-success">{{ session('success') }}</div>@endif
    <section class="dashboard-panel counter-patient-summary">
        <div class="dashboard-panel-header"><div><p class="eyebrow">Patient concern</p><h2>Visit Summary</h2></div></div>
        <div class="complaint-detail-grid">
            <div><span>Priority</span><strong>{{ $complaint->triage_priority_label }}</strong></div><div><span>Status</span><strong>{{ $complaint->status }}</strong></div>
            <div class="complaint-detail-wide"><span>Chief Complaint</span><strong>{{ $complaint->chief_complaint }}</strong></div>
            <div class="complaint-detail-wide"><span>Selected Concerns</span><p>{{ $complaint->complaintOptions->pluck('name')->implode(', ') ?: 'None selected' }}</p></div>
            <div class="complaint-detail-wide"><span>Symptoms</span><p>{{ $complaint->symptoms_description ?: 'None provided' }}</p></div>
        </div>
    </section>
    <section class="dashboard-panel counter-action-panel">
        <div class="dashboard-panel-header"><div><p class="eyebrow">Service documentation</p><h2>Action Provided</h2></div></div>
        <div class="counter-action-selector form-group"><label for="counter_action">Select action</label><select id="counter_action" class="form-control" data-counter-action><option value="counter_remedy">Counter Remedy</option><option value="basic_service">Basic Clinic Service</option><option value="forward_doctor">Forward to Doctor Consultation</option></select></div>
        <form method="POST" action="{{ route('counter-services.complete', $queue) }}" class="workflow-form workflow-form-grid" data-counter-form="service">
            @csrf <input type="hidden" name="action_type" value="counter_remedy" data-action-type>
            <div class="form-group form-group-wide"><label for="service_provided">Remedy / Service Provided</label><textarea id="service_provided" name="service_provided" class="form-control" required></textarea></div>
            <div class="form-group"><label for="quantity">Quantity</label><input id="quantity" name="quantity" class="form-control"></div>
            <div class="form-group"><label for="outcome">Outcome</label><input id="outcome" name="outcome" class="form-control" value="Resolved" required></div>
            <div class="form-group form-group-wide"><label for="notes">Notes</label><textarea id="notes" name="notes" class="form-control"></textarea></div>
            <div class="counter-service-actions"><button type="button" class="btn btn-light" data-save-counter-draft>Save Draft</button><button class="btn btn-primary" data-submit-loading="Completing...">Complete Counter Service</button><small data-counter-draft-status class="text-muted"></small></div>
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
<script>(function(){var select=document.querySelector('[data-counter-action]'),service=document.querySelector('[data-counter-form="service"]'),forward=document.querySelector('[data-counter-form="forward"]'),type=document.querySelector('[data-action-type]'),key='counter-service-draft-{{ $queue->id }}';if(!select)return;function sync(){var isForward=select.value==='forward_doctor';service.hidden=isForward;forward.hidden=!isForward;if(!isForward)type.value=select.value;}select.addEventListener('change',sync);try{var draft=JSON.parse(localStorage.getItem(key)||'{}');Object.keys(draft).forEach(function(name){var field=service.elements[name];if(field)field.value=draft[name];});if(draft.action_type)select.value=draft.action_type;}catch(error){}sync();document.querySelector('[data-save-counter-draft]').addEventListener('click',function(){var draft={};new FormData(service).forEach(function(value,name){if(name!=='_token')draft[name]=value;});localStorage.setItem(key,JSON.stringify(draft));document.querySelector('[data-counter-draft-status]').textContent='Draft saved on this device.';});service.addEventListener('submit',function(){localStorage.removeItem(key);});})();</script>
@endpush
