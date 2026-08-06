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
    </section>

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
                <label class="certificate-field">Fitness Assessment <span>*</span>
                    <select name="fitness_status" class="form-control @error('fitness_status') is-invalid @enderror" required>
                        @foreach(['physically_fit'=>'Physically Fit','physically_unfit'=>'Physically Unfit','fit_with_restrictions'=>'Fit with Restrictions','not_assessed'=>'Not Assessed','other'=>'Other'] as $value=>$label)
                            <option value="{{ $value }}" {{ old('fitness_status', $certificate->fitness_status ?: 'not_assessed') === $value ? 'selected' : '' }}>{{ $label }}</option>
                        @endforeach
                    </select>
                </label>
                <label class="certificate-field">Fitness Details
                    <textarea name="fitness_details" class="form-control @error('fitness_details') is-invalid @enderror" rows="3" placeholder="Required for restrictions or Other">{{ old('fitness_details', $certificate->fitness_details) }}</textarea>
                    @error('fitness_details')<small class="invalid-feedback">{{ $message }}</small>@enderror
                </label>
            </div>
        </section>

        <section class="certificate-form-section">
            <div class="certificate-section-heading"><span>3</span><div><h2>Purpose and validity</h2><p>Define why the certificate is requested and its applicable dates.</p></div></div>
            <div class="certificate-form-grid">
                <label class="certificate-field">Purpose <span>*</span>
                    <select name="purpose" class="form-control @error('purpose') is-invalid @enderror" required>
                        @foreach(['ojt'=>'OJT','scholarship_application'=>'Scholarship Application','employment'=>'Employment','school_requirement'=>'School Requirement','sports_activity'=>'Sports Activity','return_to_school'=>'Return to School','other'=>'Other'] as $value=>$label)
                            <option value="{{ $value }}" {{ old('purpose', $certificate->purpose ?: 'school_requirement') === $value ? 'selected' : '' }}>{{ $label }}</option>
                        @endforeach
                    </select>
                </label>
                <label class="certificate-field">Other Purpose
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
            <button type="submit" class="btn btn-secondary"><i class="fa fa-save"></i> {{ $isExisting ? 'Save Changes' : 'Save Draft' }}</button>
        </footer>
    </form>

    @if($isExisting)
        <section class="certificate-issue-card">
            <div><h2>Ready to issue?</h2><p>Issuing finalizes the certificate. It can no longer be edited afterward.</p></div>
            <form method="POST" action="{{ route('medical-certificates.issue', $certificate) }}">
                @csrf
                <label class="certificate-confirm"><input type="checkbox" name="confirm_issue" value="1" required> I reviewed the information and confirm this clinical determination.</label>
                <button class="btn btn-primary"><i class="fa fa-check-circle"></i> Issue Medical Certificate</button>
            </form>
        </section>
    @endif
</div>
@endsection
