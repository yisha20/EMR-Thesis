@extends('layouts.app')

@section('content')
<div class="dashboard-panel complaint-detail-card">
    <div class="dashboard-panel-header">
        <div><p class="eyebrow">Digital intake</p><h2>Complaint Details</h2></div>
        <a href="{{ route('student.complaints.index') }}" class="btn btn-light">Back to My Complaints</a>
    </div>
    <div class="complaint-detail-grid">
        <div><span>Complaint Category</span><strong>{{ $complaint->complaint_category ?: 'General Consultation' }}</strong></div>
        <div><span>Submitted</span><strong>{{ $complaint->submitted_at->format('M j, Y') }} at {{ $complaint->submitted_at->format('g:i A') }}</strong></div>
        <div><span>Status</span><strong><span class="complaint-status status-{{ \Illuminate\Support\Str::slug($complaint->status) }}">{{ $complaint->status }}</span></strong></div>
        <div><span>Priority</span><strong><span class="urgency-badge urgency-{{ $complaint->triage_priority }}">{{ $complaint->triage_priority_label }}</span></strong></div>
        <div><span>Reviewed</span><strong>{{ $complaint->reviewed_at ? $complaint->reviewed_at->format('M j, Y g:i A') : 'Not reviewed yet' }}</strong></div>
        <div><span>Completed</span><strong>{{ $complaint->completed_at ? $complaint->completed_at->format('M j, Y g:i A') : 'Not completed yet' }}</strong></div>
        <div class="complaint-detail-wide"><span>Chief Complaint</span><strong>{{ $complaint->chief_complaint }}</strong></div>
        <div class="complaint-detail-wide"><span>Symptoms Description</span><p>{{ $complaint->symptoms_description }}</p></div>
        @if ($complaint->attachment)
            <div><span>Attachment</span><a href="{{ route('student.complaints.attachment',$complaint) }}" target="_blank" rel="noopener">Open attachment</a></div>
        @endif
        @if ($complaint->diagnosis)
            <div class="complaint-detail-wide"><span>Diagnosis</span><p>{{ $complaint->diagnosis }}</p></div>
            <div class="complaint-detail-wide"><span>Treatment</span><p>{{ $complaint->treatment ?: 'Not specified' }}</p></div>
            <div class="complaint-detail-wide"><span>Prescription</span><p>{{ $complaint->prescription ?: 'Not specified' }}</p></div>
        @endif
    </div>
</div>
@endsection
