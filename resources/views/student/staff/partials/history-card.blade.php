<aside class="dashboard-panel complaint-history-card">
    @php
        $linkedPatient = $complaint->patient ?: $patient;
    @endphp
    <div class="dashboard-panel-header">
        <div><p class="eyebrow">Medical history</p><h2>EMR Connection</h2></div>
    </div>
    <div class="record-link-panel compact-record-link">
        @if ($linkedPatient)
            <span class="emr-status-badge active">{{ $complaint->patient ? 'Patient linked' : 'Matching IIT ID found' }}</span>
            <strong>{{ $linkedPatient->first_name }} {{ $linkedPatient->last_name }}</strong>
            <span class="text-muted">{{ $linkedPatient->id_number }}</span>
            <div class="record-action-grid">
                <a href="{{ route('patients.show', $linkedPatient->id) }}" class="btn btn-light"><i class="fa fa-user"></i> View Profile</a>
                <a href="{{ route('patients.edit', $linkedPatient->id) }}" class="btn btn-primary"><i class="fa fa-edit"></i> Edit Profile / History</a>
                <a href="{{ route('medical-records.show', $linkedPatient->id) }}" class="btn btn-light"><i class="fa fa-folder-open-o"></i> Health History</a>
            </div>
        @elseif ($matchingPatients->count())
            <span class="emr-status-badge active">Matching IIT ID found</span>
            <strong>{{ $patient->first_name }} {{ $patient->last_name }}</strong>
            <span class="text-muted">{{ $patient->id_number }}</span>
        @else
            @include('includes.empty-state', ['title' => 'Patient will be created automatically.', 'message' => 'The student profile is copied when clinic staff takes action.', 'icon' => 'fa-folder-open-o'])
        @endif
    </div>

    <div class="previous-visits-panel">
        <strong>Previous Visits</strong>
        @forelse ($previousRecords as $record)
            <a href="{{ route('medical-records.edit', $record) }}" class="previous-visit-item">
                <time>{{ optional($record->date_of_consultation)->format('M j') ?: 'No date' }}</time>
                <span>{{ $record->chief_complaint ?: $record->record_type ?: 'Medical record' }}</span>
            </a>
        @empty
            <p>No previous related records found.</p>
        @endforelse
    </div>
</aside>
