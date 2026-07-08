@extends('layouts.app')

@section('content')
<div class="border border-info medical-record-canvas">
    <div class="card-header border-info">
        <ul class="nav nav-tabs card-header-tabs">
            <li class="nav-item"><span class="nav-link active">Medical Records</span></li>
        </ul>
    </div>

    <div class="card-body">
        <div class="dashboard-heading">
            <p class="eyebrow">Clinical records</p>
            <h1>Medical Records</h1>
            <span>Review consultation history across all registered patients.</span>
        </div>

        <form method="GET" action="{{ route('medical-records.index') }}" class="emr-filter-bar filter-toolbar">
            <div class="emr-filter-search filter-search">
                <i class="fa fa-search"></i>
                <input type="search" name="search" value="{{ request('search') }}" placeholder="Search patient, ID, complaint, service, or physician" aria-label="Search medical records">
            </div>
            <input type="date" name="date" value="{{ request('date') }}" class="form-control filter-select" aria-label="Filter by consultation date">
            <div class="filter-actions">
                <button type="submit" class="btn btn-primary">Apply</button>
                <a href="{{ route('medical-records.index') }}" class="btn btn-light">Reset</a>
            </div>
        </form>

        <div class="medical-record-table-wrap table-responsive-shell">
            <table class="table table-hover medical-record-table data-table is-wide">
                <thead>
                    <tr>
                        <th>Patient</th>
                        <th>ID Number</th>
                        <th>Date</th>
                        <th>Chief Complaint</th>
                        <th>Service</th>
                        <th>Status</th>
                        <th>Attending Physician</th>
                        <th class="text-right">Action</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse ($medicalRecords as $record)
                        @php
                            $patient = $record->patient;
                            $patientName = $patient
                                ? trim($patient->last_name . ', ' . $patient->first_name . ' ' . $patient->middle_name)
                                : 'Patient unavailable';
                            $prescriptionPanelId = $record->prescription ? 'record-prescription-' . $record->prescription->id : null;
                        @endphp
                        <tr>
                            <td><strong>{{ $patientName }}</strong></td>
                            <td>{{ optional($patient)->id_number ?: 'Not assigned' }}</td>
                            <td>{{ optional($record->date_of_consultation)->format('M j, Y') ?: 'Not set' }}</td>
                            <td class="truncate-cell" title="{{ $record->chief_complaint }}">{{ $record->chief_complaint ?: 'Not specified' }}</td>
                            <td>{{ $record->performed_service ?: 'Not specified' }}</td>
                            <td><span class="complaint-status status-{{ \Illuminate\Support\Str::slug($record->consultation_status ?: 'Completed') }}">{{ $record->consultation_status ?: 'Completed' }}</span></td>
                            <td>{{ $record->attending_physician ?: 'Not assigned' }}</td>
                            <td class="action-cell">
                                <div class="table-action-group">
                                    @if ($patient)
                                        <a href="{{ route('patients.show', $patient->id) }}" class="table-action-button" aria-label="View student profile" title="View student profile" data-toggle="tooltip"><i class="fa fa-user"></i></a>
                                        <a href="{{ route('patients.edit', $patient->id) }}" class="table-action-button" aria-label="Edit profile and medical history" title="Edit profile and medical history" data-toggle="tooltip"><i class="fa fa-edit"></i></a>
                                    @endif
                                    @if ($record->prescription)
                                        <button type="button" class="table-action-button btn" aria-label="View prescription" title="View prescription" data-toggle="tooltip" data-prescription-modal="template-{{ $prescriptionPanelId }}" data-prescription-title="{{ $record->prescription->prescription_number }}"><i class="fa fa-file-text-o"></i></button>
                                    @else
                                        <a href="{{ route('medical-records.edit', $record->id) }}" class="table-action-button" aria-label="View consultation details" title="View consultation details" data-toggle="tooltip"><i class="fa fa-file-text-o"></i></a>
                                    @endif
                                </div>
                            </td>
                        </tr>
                        @if ($record->prescription)
                            <template id="template-{{ $prescriptionPanelId }}">
                                @include('prescriptions.inline-panel', ['prescription' => $record->prescription, 'panelId' => $prescriptionPanelId, 'variant' => 'medical-record-modal', 'isOpen' => true, 'showClose' => false])
                            </template>
                        @endif
                    @empty
                        <tr><td colspan="8">@include('includes.empty-state', ['title' => 'No medical records found.', 'message' => 'Adjust the search or date filter and try again.', 'icon' => 'fa-file-text-o'])</td></tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        <div class="pagination justify-content-center">{{ $medicalRecords->links() }}</div>
    </div>
</div>

<div class="modal fade medical-record-prescription-modal" id="medicalRecordPrescriptionModal" tabindex="-1" role="dialog" aria-labelledby="medicalRecordPrescriptionModalTitle" aria-hidden="true">
    <div class="modal-dialog modal-xl modal-dialog-centered modal-dialog-scrollable" role="document">
        <div class="modal-content">
            <div class="modal-header">
                <div>
                    <p class="eyebrow">Prescription document</p>
                    <h5 class="modal-title" id="medicalRecordPrescriptionModalTitle">Prescription</h5>
                </div>
                <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                    <span aria-hidden="true">&times;</span>
                </button>
            </div>
            <div class="modal-body" id="medicalRecordPrescriptionModalBody"></div>
        </div>
    </div>
</div>
@endsection
