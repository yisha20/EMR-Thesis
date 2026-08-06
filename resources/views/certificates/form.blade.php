@extends('layouts.app')

@section('content')
@php
    $patient = $consultation->patient;
    $isExisting = $certificate->exists;
@endphp

<div class="medical-certificate-page">
    <header class="certificate-page-header">
        <div>
            <p class="eyebrow">Clinical document</p>
            <h1>Medical Certificate</h1>
            <p>Complete the clinical determination, save the draft, then review it carefully before issuing.</p>
        </div>
        <span class="certificate-status is-draft"><i class="fa fa-pencil"></i> Draft</span>
    </header>

    @if(session('success'))
        <div class="alert alert-success" role="status">{{ session('success') }}</div>
    @endif

    <section class="certificate-patient-card" aria-label="Patient and consultation summary">
        <span class="certificate-patient-avatar"><i class="fa fa-user"></i></span>
        <div><small>Patient</small><strong>{{ trim(optional($patient)->first_name.' '.optional($patient)->middle_name.' '.optional($patient)->last_name) }}</strong></div>
        <div><small>ID number</small><strong>{{ optional($patient)->id_number ?: 'Not provided' }}</strong></div>
        <div><small>Age / Sex</small><strong>{{ optional($patient)->age ?? '—' }} / {{ optional($patient)->gender ?: '—' }}</strong></div>
        <div><small>Consultation completed</small><strong>{{ optional($consultation->completed_at)->format('M j, Y · g:i A') ?: '—' }}</strong></div>
        <div><small>Attending physician</small><strong>{{ auth()->user()->fullName() }}</strong></div>
        <div><small>Certificate number</small><strong>{{ $certificate->certificate_number ?: 'Assigned after saving' }}</strong></div>
        <div><small>Issue date</small><strong>{{ optional($certificate->issue_date)->format('M j, Y') ?: now()->format('M j, Y') }}</strong></div>
        <div><small>Residence</small><strong>{{ optional($patient)->present_address ?: (optional($patient)->home_address ?: 'Not provided') }}</strong></div>
    </section>

    <p class="certificate-statement-preview">This is to certify that <strong>{{ trim(optional($patient)->first_name.' '.optional($patient)->middle_name.' '.optional($patient)->last_name) }}</strong>, <strong>{{ optional($patient)->age ?? '—' }}</strong> years old, <strong>{{ optional($patient)->gender ?: '—' }}</strong>, a resident of <strong>{{ optional($patient)->present_address ?: (optional($patient)->home_address ?: 'Not provided') }}</strong>, was seen and evaluated at the MSU-IIT Clinic.</p>

    <form method="POST" action="{{ $isExisting ? route('medical-certificates.update', $certificate) : route('consultations.medical-certificates.store', $consultation) }}" class="certificate-editor-card">
        @csrf
        @if($isExisting) @method('PUT') @endif

        <section class="certificate-form-section">
            <div class="certificate-section-heading"><span>1</span><div><h2>Clinical findings</h2><p>Record only findings supported by the completed consultation.</p></div></div>
            <div class="certificate-form-grid">
                <label class="certificate-field is-full">Reason for Visit <span>*</span>
                    <textarea name="reason_for_visit" class="form-control @error('reason_for_visit') is-invalid @enderror" rows="3" required>{{ old('reason_for_visit', $certificate->reason_for_visit ?: optional($consultation->complaint)->chief_complaint) }}</textarea>
                    @error('reason_for_visit')<small class="invalid-feedback">{{ $message }}</small>@enderror
                </label>
                <fieldset class="certificate-checks is-full">
                    <legend>Examination performed</legend>
                    <label><input type="checkbox" name="consultation_performed" value="1" {{ old('consultation_performed', $isExisting ? $certificate->consultation_performed : true) ? 'checked' : '' }}> Consultation</label>
                    <label><input type="checkbox" name="physical_examination_performed" value="1" {{ old('physical_examination_performed', $certificate->physical_examination_performed) ? 'checked' : '' }}> Physical examination</label>
                </fieldset>
                <label class="certificate-field is-full">Clinical Impression <span>*</span>
                    <textarea name="clinical_impression" class="form-control @error('clinical_impression') is-invalid @enderror" rows="4" required>{{ old('clinical_impression', $certificate->clinical_impression ?: $consultation->assessment) }}</textarea>
                    @error('clinical_impression')<small class="invalid-feedback">{{ $message }}</small>@enderror
                </label>
            </div>
        </section>

        <section class="certificate-form-section">
            <div class="certificate-section-heading"><span>2</span><div><h2>Fitness determination</h2><p>Select the outcome and document any restrictions.</p></div></div>
            <div class="certificate-form-grid">
                <fieldset class="certificate-option-group is-full @error('fitness_status') is-invalid @enderror">
                    <legend>Fitness Assessment <span>*</span></legend>
                    @foreach(['physically_fit'=>'Physically Fit','physically_unfit'=>'Physically Unfit','fit_with_restrictions'=>'Fit with Restrictions','other'=>'Other'] as $value=>$label)
                        <label><input type="radio" name="fitness_status" value="{{ $value }}" {{ old('fitness_status', $certificate->fitness_status ?: 'physically_fit') === $value ? 'checked' : '' }} required> {{ $label }}</label>
                    @endforeach
                    @error('fitness_status')<small class="invalid-feedback d-block">{{ $message }}</small>@enderror
                </fieldset>
                <label class="certificate-field is-full" data-fitness-details>Restriction / Other Fitness Details
                    <textarea name="fitness_details" class="form-control @error('fitness_details') is-invalid @enderror" rows="3" placeholder="Required for restrictions or Other">{{ old('fitness_details', $certificate->fitness_details) }}</textarea>
                    @error('fitness_details')<small class="invalid-feedback">{{ $message }}</small>@enderror
                </label>
            </div>
        </section>

        <section class="certificate-form-section">
            <div class="certificate-section-heading"><span>3</span><div><h2>Purpose and validity</h2><p>Define why the certificate is requested and its applicable dates.</p></div></div>
            <div class="certificate-form-grid">
                <fieldset class="certificate-option-group is-full @error('purpose') is-invalid @enderror">
                    <legend>Purpose <span>*</span></legend>
                    @foreach(['ojt'=>'OJT','scholarship_application'=>'Scholarship Application','employment'=>'Employment','school_requirement'=>'School Requirement','sports_activity'=>'Sports Participation','return_to_school'=>'Return to School','travel_requirement'=>'Travel Requirement','other'=>'Other'] as $value=>$label)
                        <label><input type="radio" name="purpose" value="{{ $value }}" {{ old('purpose', $certificate->purpose ?: 'school_requirement') === $value ? 'checked' : '' }} required> {{ $label }}</label>
                    @endforeach
                    @error('purpose')<small class="invalid-feedback d-block">{{ $message }}</small>@enderror
                </fieldset>
                <label class="certificate-field is-full" data-other-purpose>Other Purpose
                    <input name="purpose_other" class="form-control @error('purpose_other') is-invalid @enderror" value="{{ old('purpose_other', $certificate->purpose_other) }}" placeholder="Required when purpose is Other">
                    @error('purpose_other')<small class="invalid-feedback">{{ $message }}</small>@enderror
                </label>
                <label class="certificate-field">Valid From
                    <input type="date" name="valid_from" class="form-control @error('valid_from') is-invalid @enderror" value="{{ old('valid_from', optional($certificate->valid_from)->format('Y-m-d')) }}">
                </label>
                <label class="certificate-field">Valid Until
                    <input type="date" name="valid_until" class="form-control @error('valid_until') is-invalid @enderror" value="{{ old('valid_until', optional($certificate->valid_until)->format('Y-m-d')) }}">
                    @error('valid_until')<small class="invalid-feedback">{{ $message }}</small>@enderror
                </label>
                <label class="certificate-field is-full">Remarks
                    <textarea name="remarks" class="form-control @error('remarks') is-invalid @enderror" rows="3">{{ old('remarks', $certificate->remarks) }}</textarea>
                    @error('remarks')<small class="invalid-feedback">{{ $message }}</small>@enderror
                </label>
            </div>
        </section>

        <footer class="certificate-form-actions">
            <a href="{{ url()->previous() }}" class="btn btn-light">Cancel</a>
            @if($isExisting)<a href="{{ route('medical-certificates.show', $certificate) }}" class="btn btn-outline-primary"><i class="fa fa-eye"></i> Preview Certificate</a>@endif
            <button type="submit" class="btn btn-secondary"><i class="fa fa-save"></i> {{ $isExisting ? 'Save Changes' : 'Save Draft' }}</button>
        </footer>
    </form>

    @if($isExisting)
        <section class="certificate-issue-card">
            <div><h2>Ready to issue?</h2><p>Issuing finalizes the certificate. It can no longer be edited afterward.</p>
                <dl class="certificate-issue-summary"><dt>Patient</dt><dd>{{ $certificate->patient_name_snapshot }}</dd><dt>Fitness</dt><dd>{{ $certificate->fitness_label }}</dd><dt>Purpose</dt><dd>{{ $certificate->purpose_label }}</dd><dt>Doctor</dt><dd>{{ $certificate->doctor_name_snapshot }}</dd><dt>Certificate</dt><dd>{{ $certificate->certificate_number }}</dd></dl>
            </div>
            <form method="POST" action="{{ route('medical-certificates.issue', $certificate) }}">
                @csrf
                <label class="certificate-confirm"><input type="checkbox" name="confirm_issue" value="1" required> I reviewed the information and confirm this clinical determination.</label>
                <button class="btn btn-primary"><i class="fa fa-check-circle"></i> Issue Medical Certificate</button>
            </form>
        </section>
    @endif
</div>
@endsection

@push('js')
<script>
(function () {
    var fitnessDetails = document.querySelector('[data-fitness-details]');
    var otherPurpose = document.querySelector('[data-other-purpose]');
    function selected(name) { var input = document.querySelector('input[name="' + name + '"]:checked'); return input ? input.value : ''; }
    function syncOptions() {
        if (fitnessDetails) fitnessDetails.hidden = ['fit_with_restrictions', 'other'].indexOf(selected('fitness_status')) === -1;
        if (otherPurpose) otherPurpose.hidden = selected('purpose') !== 'other';
    }
    document.querySelectorAll('input[name="fitness_status"], input[name="purpose"]').forEach(function (input) { input.addEventListener('change', syncOptions); });
    syncOptions();
})();
</script>
@endpush
