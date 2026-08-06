@extends('layouts.app')

@section('content')
@php
    $isIssued = $certificate->status === 'issued';
    $roleName = optional(auth()->user()->role)->name;
    $consultationUrl = $certificate->consultation && $certificate->consultation->student_complaint_id
        ? route('student-complaints.show', $certificate->consultation->student_complaint_id)
        : ($roleName === 'Student' ? route('student.medical-history') : route('dashboard'));
@endphp
<div class="medical-certificate-page">
    <header class="certificate-page-header no-print">
        <div><p class="eyebrow">Clinical document</p><h1>Medical Certificate</h1><p>{{ $certificate->certificate_number }} · {{ $isIssued ? 'Issued and read-only' : 'Draft preview' }}</p></div>
        <span class="certificate-status {{ $isIssued ? 'is-issued' : 'is-draft' }}"><i class="fa {{ $isIssued ? 'fa-check-circle' : 'fa-pencil' }}"></i> {{ ucfirst($certificate->status) }}</span>
    </header>

    @if(session('success'))<div class="alert alert-success no-print" role="status">{{ session('success') }}</div>@endif
    @if(session('pdf_error'))<div class="alert alert-danger no-print" role="alert">{{ session('pdf_error') }}</div>@endif

    <nav class="certificate-preview-actions no-print" aria-label="Certificate actions">
        <a href="{{ $consultationUrl }}" class="btn btn-light"><i class="fa fa-arrow-left"></i> Back to Consultation</a>
        @if(!$isIssued && (int)$certificate->issued_by_doctor_id === (int)auth()->id())<a href="{{ route('medical-certificates.edit', $certificate) }}" class="btn btn-secondary"><i class="fa fa-pencil"></i> Edit Draft</a>@endif
        @if($isIssued)
            <a href="{{ route('medical-certificates.pdf', $certificate) }}" class="btn btn-primary"><i class="fa fa-download"></i> Download PDF</a>
            <a href="{{ route('medical-certificates.print', $certificate) }}" target="_blank" rel="noopener" class="btn btn-light"><i class="fa fa-print"></i> Print</a>
        @endif
    </nav>

    <p class="certificate-print-note no-print">For a clean print, disable browser “Headers and footers” in the print settings.</p>
    <div class="certificate-sheet-stage">
        @include('certificates.partials.certificate-content')
    </div>
</div>
@endsection
