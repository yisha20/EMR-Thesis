@extends('layouts.app')

@section('content')
<div class="student-portal-page">
    @if ($message = Session::get('success'))
        <div class="alert alert-success">{{ $message }}</div>
    @endif

    <header class="student-welcome">
        <div>
            <p class="eyebrow">Digital intake</p>
            <h1>My Complaints</h1>
            <p>Submit a clinic concern and review your previous digital intake records.</p>
        </div>
        <button type="button" class="btn btn-primary" data-toggle="modal" data-target="#studentConcernModal">
            <i class="fa fa-plus"></i> Submit New Concern
        </button>
    </header>

    <section class="dashboard-panel">
        <div class="dashboard-panel-header">
            <div><p class="eyebrow">Submission history</p><h2>My Complaint History</h2></div>
        </div>
        <div class="table-responsive-shell">
            <table class="table emr-data-table data-table student-complaint-history">
                <thead><tr><th>Submitted</th><th>Chief Complaint</th><th>Urgency</th><th>Status</th><th>Action</th></tr></thead>
                <tbody>
                    @forelse ($complaints as $complaint)
                        <tr>
                            <td>{{ $complaint->submitted_at->format('M j, Y') }}<small class="d-block text-muted">{{ $complaint->submitted_at->format('g:i A') }}</small></td>
                            <td><strong>{{ $complaint->chief_complaint }}</strong><small class="d-block text-muted">{{ $complaint->complaint_category ?: 'General Consultation' }}</small></td>
                            <td><span class="urgency-badge urgency-{{ strtolower($complaint->urgency_level) }}">{{ $complaint->urgency_level }}</span></td>
                            <td><span class="complaint-status status-{{ \Illuminate\Support\Str::slug($complaint->status) }}">{{ $complaint->status }}</span></td>
                            <td class="action-cell"><a href="{{ route('student.complaints.show', $complaint) }}" class="table-action-button" aria-label="View complaint details" title="View complaint details" data-toggle="tooltip"><i class="fa fa-eye"></i></a></td>
                        </tr>
                    @empty
                        <tr><td colspan="5">@include('includes.empty-state', ['title' => 'No complaints submitted yet.', 'message' => 'Your digital intake history will appear here.', 'icon' => 'fa-file-text-o'])</td></tr>
                    @endforelse
                </tbody>
            </table>
        </div>
        <div class="pagination justify-content-center">{{ $complaints->links() }}</div>
    </section>

    @include('student.complaints.partials.intake-modal')
</div>
@endsection
