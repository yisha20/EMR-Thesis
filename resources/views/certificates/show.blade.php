@extends('layouts.app')

@section('content')
<div class="medical-certificate-page">
    <header class="certificate-page-header">
        <div><p class="eyebrow">Clinical document</p><h1>Medical Certificate</h1><p>{{ $certificate->certificate_number }}</p></div>
        <span class="certificate-status is-issued"><i class="fa fa-check-circle"></i> Issued</span>
    </header>

    @if(session('success'))<div class="alert alert-success" role="status">{{ session('success') }}</div>@endif

    <section class="issued-certificate-card">
        <header><img src="{{ asset('img/msu-iit-logo.png') }}" alt="MSU-IIT seal"><div><strong>MSU-IIT Clinic</strong><span>Official Medical Certificate</span></div></header>
        <div class="issued-certificate-meta">
            <div><small>Certificate number</small><strong>{{ $certificate->certificate_number }}</strong></div>
            <div><small>Issue date</small><strong>{{ optional($certificate->issue_date)->format('F j, Y') }}</strong></div>
            <div><small>Patient</small><strong>{{ $certificate->patient_name_snapshot }}</strong></div>
            <div><small>ID number</small><strong>{{ $certificate->patient_id_snapshot ?: 'Not provided' }}</strong></div>
        </div>
        <dl class="issued-certificate-details">
            <div><dt>Reason for visit</dt><dd>{{ $certificate->reason_for_visit }}</dd></div>
            <div><dt>Clinical impression</dt><dd>{{ $certificate->clinical_impression }}</dd></div>
            <div><dt>Fitness assessment</dt><dd>{{ ucwords(str_replace('_', ' ', $certificate->fitness_status)) }}{{ $certificate->fitness_details ? ' — '.$certificate->fitness_details : '' }}</dd></div>
            <div><dt>Purpose</dt><dd>{{ ucwords(str_replace('_', ' ', $certificate->purpose)) }}{{ $certificate->purpose_other ? ' — '.$certificate->purpose_other : '' }}</dd></div>
            @if($certificate->valid_from || $certificate->valid_until)<div><dt>Validity</dt><dd>{{ optional($certificate->valid_from)->format('M j, Y') ?: 'Not specified' }} to {{ optional($certificate->valid_until)->format('M j, Y') ?: 'Not specified' }}</dd></div>@endif
            @if($certificate->remarks)<div><dt>Remarks</dt><dd>{{ $certificate->remarks }}</dd></div>@endif
        </dl>
        <footer><span>Issued by <strong>{{ $certificate->doctor_name_snapshot }}</strong></span><span>{{ optional($certificate->issued_at)->format('M j, Y · g:i A') }}</span></footer>
    </section>

    <div class="issued-certificate-actions">
        <a href="{{ route('medical-certificates.pdf', $certificate) }}" class="btn btn-primary"><i class="fa fa-download"></i> Download PDF</a>
        <button type="button" class="btn btn-light" onclick="window.print()"><i class="fa fa-print"></i> Print</button>
    </div>
</div>
@endsection
