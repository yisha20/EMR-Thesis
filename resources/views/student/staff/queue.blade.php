@extends('layouts.app')

@section('content')
@php
    $roleName = auth()->user()->role->name;
    $isDoctorQueue = $roleName === 'Doctor';
    $canReview = in_array($roleName, ['Administrator', 'Nurse', 'Staff']);
@endphp
<div class="dashboard-wrap">
    <div class="dashboard-heading">
        <p class="eyebrow">Student digital intake</p>
        <h1>{{ $isDoctorQueue ? 'Consultation Queue' : 'Complaint Queue' }}</h1>
        <span>{{ $isDoctorQueue ? 'Review complaints forwarded for doctor consultation.' : 'Review incoming student concerns and choose the appropriate clinic path.' }}</span>
    </div>

    <form method="GET" action="{{ route('student-complaints.index') }}" class="emr-filter-bar filter-toolbar">
        <div class="emr-filter-search filter-search">
            <i class="fa fa-id-card-o"></i>
            <input name="student_id" value="{{ request('student_id') }}" placeholder="Search by exact IIT ID number" aria-label="Search by IIT ID number">
        </div>
        <select name="status" class="form-control filter-select" aria-label="Filter complaint status">
            <option value="">All statuses</option>
            @foreach (['Pending', 'Reviewed', 'Forwarded', 'In Consultation', 'Counter Resolved', 'Completed'] as $status)
                <option value="{{ $status }}" {{ request('status') === $status ? 'selected' : '' }}>{{ $status }}</option>
            @endforeach
        </select>
        <div class="filter-actions">
            <button class="btn btn-primary" type="submit">Search Queue</button>
            <a href="{{ route('student-complaints.index') }}" class="btn btn-light">Reset</a>
        </div>
    </form>

    @if (request('student_id'))
        <section class="dashboard-panel student-search-result">
            <div class="dashboard-panel-header"><div><p class="eyebrow">IIT ID search</p><h2>Student Profile</h2></div></div>
            @if ($searchedStudent)
                <div class="student-profile-list student-search-profile">
                    <div><span>IIT ID</span><strong>{{ $searchedStudent->student_id_number }}</strong></div>
                    <div><span>Name</span><strong>{{ $searchedStudent->full_name }}</strong></div>
                    <div><span>Department</span><strong>{{ $searchedStudent->college_department }}</strong></div>
                    <div><span>Contact</span><strong>{{ $searchedStudent->contact_number }}</strong></div>
                    <div><span>Complaint History</span><strong>{{ $searchedStudent->complaints->count() }}</strong></div>
                </div>
            @else
                @include('includes.empty-state', ['title' => 'No student found for this IIT ID.', 'icon' => 'fa-id-card-o'])
            @endif
        </section>
    @endif

    <section class="dashboard-panel complaint-queue-panel">
        <div class="dashboard-panel-header"><div><p class="eyebrow">Current workload</p><h2>Submitted Complaints</h2></div></div>
        <div class="table-responsive-shell">
            <table class="table emr-data-table complaint-queue-table data-table is-wide">
                <thead><tr><th>IIT ID</th><th>Student</th><th>Chief Complaint</th><th>Urgency</th><th>Submitted</th><th>Status</th><th class="text-right">Actions</th></tr></thead>
                <tbody>
                    @forelse ($complaints as $complaint)
                        <tr>
                            <td>{{ $complaint->student_id_number }}</td>
                            <td><strong>{{ $complaint->student_name }}</strong></td>
                            <td>{{ $complaint->chief_complaint }}</td>
                            <td><span class="urgency-badge urgency-{{ strtolower($complaint->triage_priority) }}">{{ ucfirst($complaint->triage_priority) }}</span></td>
                            <td>{{ $complaint->submitted_at->format('M j, Y') }}<small class="d-block text-muted">{{ $complaint->submitted_at->format('g:i A') }}</small></td>
                            <td><span class="complaint-status status-{{ \Illuminate\Support\Str::slug($complaint->status) }}">{{ $complaint->status }}</span></td>
                            <td class="action-cell">
                                <div class="table-action-group">
                                    <a href="{{ route('student-complaints.show', $complaint) }}" class="table-action-button" aria-label="View complaint" title="View complaint" data-toggle="tooltip"><i class="fa fa-eye"></i></a>
                                    @if ($canReview && $complaint->status === 'Pending')
                                        <form method="POST" action="{{ route('student-complaints.status', $complaint) }}">@csrf @method('PATCH')
                                            <input type="hidden" name="status" value="Reviewed">
                                            <button class="table-action-button btn" aria-label="Mark as reviewed" title="Mark as reviewed" data-toggle="tooltip" data-confirm="Mark this complaint as reviewed?" data-confirm-title="Review complaint"><i class="fa fa-check"></i></button>
                                        </form>
                                    @endif
                                </div>
                            </td>
                        </tr>
                    @empty
                        <tr><td colspan="7">@include('includes.empty-state', ['title' => 'No student complaints in this queue.', 'message' => 'New digital intake submissions will appear automatically.', 'icon' => 'fa-inbox'])</td></tr>
                    @endforelse
                </tbody>
            </table>
        </div>
        <div class="pagination justify-content-center">{{ $complaints->links() }}</div>
    </section>
</div>
@endsection
