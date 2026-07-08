@extends('layouts.app')

@section('content')
<div class="medical-record-canvas health-history-page">
    <div class="health-history-header">
        <div>
            <p class="eyebrow">Patient medical record</p>
            <h1>Health History</h1>
            <span>Workflow-generated counter services and doctor consultations.</span>
        </div>
        <a href="{{ route('patients.show', $patient) }}" class="btn btn-light"><i class="fa fa-arrow-left"></i> Patient Profile</a>
    </div>

    <section class="medical-record-banner">
        <div class="medical-record-banner-title"><p class="eyebrow">Patient data</p><h2>{{ trim($patient->first_name . ' ' . $patient->middle_name . ' ' . $patient->last_name) }}</h2></div>
        <div class="medical-record-patient-grid">
            <div><span>OPD / ID Number</span><strong>{{ $patient->id_number ?: 'Not assigned' }}</strong></div>
            <div><span>Department</span><strong>{{ $patient->college_department ?: 'Not specified' }}</strong></div>
            <div><span>Age</span><strong>{{ $patient->age ?: 'Not specified' }}</strong></div>
            <div><span>Gender</span><strong>{{ $patient->gender ?: 'Not specified' }}</strong></div>
            <div><span>Contact</span><strong>{{ $patient->phone_number ?: 'Not specified' }}</strong></div>
        </div>
    </section>

    @include('medicalreport.timeline', ['records' => $patient->medicalRecords->sortByDesc(function ($record) { return $record->date_of_consultation ? $record->date_of_consultation->format('Y-m-d') . ' ' . ($record->time_of_consultation ?: '') : $record->created_at; }), 'studentView' => false])
</div>
@endsection
