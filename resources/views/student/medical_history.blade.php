@extends('layouts.app')

@section('content')
<div class="medical-record-canvas health-history-page">
    <div class="health-history-header">
        <div><p class="eyebrow">Student health record</p><h1>My Health History</h1><span>Clinic actions saved from your submitted concerns.</span></div>
        <a href="{{ route('student.dashboard') }}" class="btn btn-light"><i class="fa fa-arrow-left"></i> Dashboard</a>
    </div>
    @include('medicalreport.timeline', ['records' => $records, 'studentView' => true])
    @if($patient)@foreach($patient->emergencyEncounters->sortByDesc('arrival_at') as $item)<article class="card card-body mb-3"><strong>{{ $item->encounter_type==='emergency'?'Emergency Encounter':'Walk-in Visit' }}</strong><span>{{$item->arrival_at}} · {{ucfirst($item->triage_priority)}} · {{$item->primary_concern}} · {{str_replace('_',' ',$item->status)}}</span></article>@endforeach
    @foreach($patient->medicalCertificates->where('status','issued')->sortByDesc('issued_at') as $item)<article class="card card-body mb-3"><strong>Medical Certificate Issued</strong><span>{{$item->issued_at}} · {{str_replace('_',' ',$item->purpose)}} · {{str_replace('_',' ',$item->fitness_status)}} · {{$item->doctor_name_snapshot}}</span><a href="{{route('medical-certificates.show',$item)}}">View Certificate</a> <a href="{{route('medical-certificates.pdf',$item)}}">Download PDF</a></article>@endforeach @endif
</div>
@endsection
