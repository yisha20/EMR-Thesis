@extends('layouts.app')

@section('content')
<div class="card border-info patient-table-card">
    <div class="card-header border-info patient-table-header">
        <ul class="nav nav-tabs card-header-tabs">
            <li class="nav-item"><a class="nav-link active" href="{{ route('patients.index') }}">Patients</a></li>
            <li class="nav-item"><a class="nav-link" href="{{ route('patients.create') }}">Add New Patient</a></li>
            <li class="nav-item"><a class="nav-link" href="{{ route('patients.archive') }}">Archive</a></li>
        </ul>
    </div>
    <div class="card-body">
        <form method="GET" action="{{ route('patients.index') }}" class="emr-filter-bar filter-toolbar">
            <div class="emr-filter-search filter-search">
                <i class="fa fa-search"></i>
                <input type="search" name="search" value="{{ request('search') }}" placeholder="Search by name, ID number, or department" aria-label="Search patients">
            </div>
            <select name="gender" class="form-control filter-select" aria-label="Filter by gender">
                <option value="">All genders</option>
                @foreach (['Male', 'Female'] as $gender)
                    <option value="{{ $gender }}" {{ request('gender') === $gender ? 'selected' : '' }}>{{ $gender }}</option>
                @endforeach
            </select>
            <select name="department" class="form-control filter-select" aria-label="Filter by college or department">
                <option value="">All departments</option>
                @foreach ($departments as $department)
                    <option value="{{ $department }}" {{ request('department') === $department ? 'selected' : '' }}>{{ $department }}</option>
                @endforeach
            </select>
            <select name="status" class="form-control filter-select" aria-label="Filter by status">
                <option value="">All statuses</option>
                @foreach (['Active', 'Inactive'] as $status)
                    <option value="{{ $status }}" {{ request('status') === $status ? 'selected' : '' }}>{{ $status }}</option>
                @endforeach
            </select>
            <div class="filter-actions">
                <button class="btn btn-primary" type="submit">Apply</button>
                <a class="btn btn-light" href="{{ route('patients.index') }}">Reset</a>
            </div>
        </form>

        <div class="table-responsive-shell emr-data-table-wrap">
            <table class="table table-hover patient-data-table emr-data-table data-table is-wide">
                <thead>
                    <tr>
                        <th>Patient</th>
                        <th>ID Number</th>
                        <th>Department</th>
                        <th>Status</th>
                        <th>Date Registered</th>
                        <th>Last Updated</th>
                        <th class="text-right">Action</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse ($patients as $patient)
                        <tr>
                            <td title="{{ $patient->last_name }}, {{ $patient->first_name }}">
                                <div class="patient-table-identity">
                                    <img src="{{ $patient->avatar ?? asset('img/no_avatar.jpg') }}" alt="" class="patient-table-avatar" onerror="this.onerror=null;this.src='{{ asset('img/no_avatar.jpg') }}';">
                                    <div><strong>{{ $patient->last_name }}, {{ $patient->first_name }}</strong><span>{{ $patient->gender ?: 'Gender not set' }}</span></div>
                                </div>
                            </td>
                            <td title="{{ $patient->id_number ?: 'Not assigned' }}">{{ $patient->id_number ?: 'Not assigned' }}</td>
                            <td title="{{ $patient->college_department ?: 'Not specified' }}">{{ $patient->college_department ?: 'Not specified' }}</td>
                            <td>
                                <div class="patient-status-stack">
                                    <span class="emr-status-badge {{ strtolower($patient->status ?: 'active') }}">{{ $patient->status ?: 'Active' }}</span>
                                    @if ($patient->profile_status === 'provisional')
                                        <span class="patient-record-label provisional" title="Identity details can be completed without creating a patient portal account.">Provisional</span>
                                    @elseif (!$patient->patientAccount)
                                        <span class="patient-record-label" title="This medical record is managed by the clinic and has no patient portal account.">Clinic only</span>
                                    @else
                                        <span class="patient-record-label linked">Portal linked</span>
                                    @endif
                                </div>
                            </td>
                            <td>{{ optional($patient->date_registered ?: $patient->created_at)->format('M j, Y') }}</td>
                            <td>{{ $patient->updated_at->format('M j, Y') }}</td>
                            <td class="action-cell">
                                <form action="{{ route('patients.destroy', $patient->id) }}" class="table-action-group" method="post">
                                    @csrf @method('DELETE')
                                    <a href="{{ route('patients.show', $patient->id) }}" class="table-action-button" aria-label="View patient" title="View patient" data-toggle="tooltip"><i class="fa fa-eye"></i></a>
                                    <a href="{{ route('patients.edit', $patient->id) }}" class="table-action-button" aria-label="Edit patient" title="Edit patient" data-toggle="tooltip"><i class="fa fa-edit"></i></a>
                                    <button class="table-action-button table-action-danger btn" type="submit" aria-label="Archive patient" title="Archive patient" data-toggle="tooltip" data-confirm="Archive {{ $patient->first_name }} {{ $patient->last_name }}?" data-confirm-title="Archive patient"><i class="fa fa-archive"></i></button>
                                </form>
                            </td>
                        </tr>
                    @empty
                        <tr><td colspan="7">@include('includes.empty-state', ['title' => 'No patients found.', 'message' => 'Adjust the search or filters and try again.', 'icon' => 'fa-user-o'])</td></tr>
                    @endforelse
                </tbody>
            </table>
        </div>
        <div class="pagination justify-content-center">{{ $patients->links() }}</div>
    </div>
</div>
@stop
