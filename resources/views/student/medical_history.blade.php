@extends('layouts.app')

@section('content')
<div class="medical-record-canvas health-history-page">
    <div class="health-history-header">
        <div><p class="eyebrow">Student health record</p><h1>My Health History</h1><span>Clinic actions saved from your submitted concerns.</span></div>
        <a href="{{ route('student.dashboard') }}" class="btn btn-light"><i class="fa fa-arrow-left"></i> Dashboard</a>
    </div>
    @include('medicalreport.timeline', ['records' => $records, 'studentView' => true])
</div>
@endsection
