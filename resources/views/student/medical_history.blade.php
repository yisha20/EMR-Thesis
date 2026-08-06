@extends('layouts.app')

@section('content')
<div class="medical-record-canvas health-history-page">
    <div class="health-history-header">
        <div><p class="eyebrow">Student health record</p><h1>My Health History</h1><span>Clinic actions saved from your submitted concerns.</span></div>
        <a href="{{ route('student.dashboard') }}" class="btn btn-light"><i class="fa fa-arrow-left"></i> Dashboard</a>
    </div>

    @include('medicalreport.timeline', ['records' => $records, 'studentView' => true])

    @if($patient && $patient->medicalCertificates->where('status', 'issued')->isNotEmpty())
        <section class="health-certificate-section" aria-labelledby="medical-certificates-heading">
            <header class="health-certificate-section-header">
                <div><p class="eyebrow">Official documents</p><h2 id="medical-certificates-heading">Medical Certificates</h2></div>
                <span>{{ $patient->medicalCertificates->where('status', 'issued')->count() }} issued</span>
            </header>

            <div class="health-certificate-list">
                @foreach($patient->medicalCertificates->where('status', 'issued')->sortByDesc('issued_at') as $item)
                    <article class="health-certificate-card">
                        <div class="health-certificate-icon" aria-hidden="true"><i class="fa fa-file-text-o"></i></div>
                        <div class="health-certificate-content">
                            <div class="health-certificate-heading">
                                <div><small>{{ $item->certificate_number }}</small><h3>Medical Certificate</h3></div>
                                <span class="certificate-status is-issued"><i class="fa fa-check-circle"></i> Issued</span>
                            </div>
                            <dl class="health-certificate-details">
                                <div><dt>Issue date</dt><dd>{{ optional($item->issued_at)->format('M j, Y · g:i A') }}</dd></div>
                                <div><dt>Purpose</dt><dd>{{ $item->purpose_label }}</dd></div>
                                <div><dt>Fitness</dt><dd>{{ $item->fitness_label }}</dd></div>
                                <div><dt>Attending physician</dt><dd>{{ $item->doctor_name_snapshot }}</dd></div>
                            </dl>
                            <div class="health-certificate-actions">
                                <a href="{{ route('medical-certificates.show', $item) }}" class="btn btn-primary"><i class="fa fa-eye"></i> View Certificate</a>
                                <a href="{{ route('medical-certificates.pdf', $item) }}" class="btn btn-light"><i class="fa fa-download"></i> Download PDF</a>
                            </div>
                        </div>
                    </article>
                @endforeach
            </div>
        </section>
    @endif
</div>
@endsection
