@extends('layouts.app')
@section('content')
@php($personal=$assessment->personal_information ?: [])
<div class="assessment-record-page">
    <header class="assessment-record-header"><div><p class="eyebrow">Patient health record</p><h1>Complete Health Assessment</h1><p>Record #{{$assessment->id}} · Version {{$assessment->version}}</p></div><a class="btn btn-primary" href="{{route('health-assessments.pdf',$assessment)}}"><i class="fa fa-download"></i> Download PDF</a></header>
    <section class="dashboard-panel"><h2>Patient Information</h2><dl class="assessment-summary-list">@foreach(['first_name'=>'First name','last_name'=>'Last name','birth_date'=>'Birth date','age'=>'Age','sex'=>'Sex','civil_status'=>'Civil status','college_department'=>'Department','home_address'=>'Home address','present_address'=>'Present address','mobile_number'=>'Mobile','email'=>'Email'] as $key=>$label)<dt>{{$label}}</dt><dd>{{$personal[$key] ?? 'Not provided'}}</dd>@endforeach</dl></section>
    <div class="assessment-record-grid">
        <section class="dashboard-panel"><h2>Past Medical History</h2>@forelse($assessment->medicalHistories as $item)<article class="assessment-record-item"><strong>{{$item->condition}}</strong><p>{{$item->notes ?: 'No additional details.'}}</p></article>@empty<p>None reported.</p>@endforelse</section>
        <section class="dashboard-panel"><h2>Family and Social History</h2><p>{{$assessment->familyHistories->pluck('condition')->implode(', ') ?: 'No family history reported.'}}</p><p>Smoking: {{data_get($assessment,'social_history.smoking_status','Not reported')}}<br>Alcohol: {{data_get($assessment,'social_history.drinks_alcohol','Not reported')}}</p><h3>Current medications</h3><p>{{$assessment->medications->pluck('medication')->implode(', ') ?: 'None reported.'}}</p></section>
    </div>
    <section class="dashboard-panel"><h2>Clinic Staff Sections</h2><p>Physical examination, vital signs, nursing interventions, and physician assessment are restricted to authorized clinic staff and are displayed in the printable record.</p></section>
</div>
@endsection
