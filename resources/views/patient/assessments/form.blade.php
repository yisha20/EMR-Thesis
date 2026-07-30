@extends('layouts.app')
@section('content')
@php
    $personal = optional($assessment)->personal_information ?: [];
    $social = optional($assessment)->social_history ?: [];
    $women = optional($assessment)->womens_health ?: [];
    $selectedMedical = optional($assessment)->medicalHistories ? $assessment->medicalHistories->pluck('condition')->all() : [];
    $selectedFamily = optional($assessment)->familyHistories ? $assessment->familyHistories->pluck('condition')->all() : [];
    $otherFamilyHistory = optional($assessment)->familyHistories ? optional($assessment->familyHistories->firstWhere('condition', 'Other hereditary disease'))->details : null;
    $hasMedications = optional($assessment)->medications && optional($assessment)->medications->isNotEmpty();
@endphp
<div class="assessment-page">
    <div class="assessment-heading">
        <div><p class="eyebrow">Required onboarding</p><h1>Complete Your Health Assessment</h1>
        <p>Please complete the required Health Assessment Record before accessing the MSU-IIT Clinic Patient Portal.</p></div>
        <span class="badge badge-info">{{ ucfirst($account->patient_type) }}</span>
    </div>
    @if(session('success'))<div class="alert alert-success">{{ session('success') }}</div>@endif
    <form method="POST" enctype="multipart/form-data" class="assessment-form" data-assessment-form>
        @csrf
        <div class="assessment-progress" aria-label="Form progress"><span class="active">A Personal</span><span>B Medical</span><span>C Family &amp; Social</span><span>D Review</span></div>
        <section class="assessment-step active" data-step="0">
            <h2>Personal and Identification Information</h2>
            <div class="assessment-photo-field">
                <div class="assessment-photo-preview">
                    <img data-formal-photo-preview src="{{ optional($account->user)->avatar ?: asset('img/no_avatar.jpg') }}" alt="Formal photo preview">
                </div>
                <label for="formal_photo">
                    Formal 1×1 photo
                    <small>Upload a recent, clear, front-facing formal photo with a plain background. It will automatically appear in the 1×1 photo area of your Health Assessment Record.</small>
                    <input id="formal_photo" type="file" name="formal_photo" class="form-control-file" accept=".jpg,.jpeg,.png,image/jpeg,image/png" {{ optional($account->user)->avatar ? '' : 'required' }}>
                    @if(optional($account->user)->avatar)<small class="text-success">A photo is already on file. Upload a new one only if it needs replacing.</small>@endif
                </label>
            </div>
            <div class="assessment-grid">
                <label>Applicant type<input class="form-control" value="{{ ucfirst($account->patient_type) }}" disabled></label>
                <label>OPD/ID Number<input name="opd_number" class="form-control" value="{{ old('opd_number', $personal['opd_number'] ?? $account->identifier) }}"></label>
                <label>Examination date<input type="date" name="examination_date" class="form-control" required value="{{ old('examination_date', $personal['examination_date'] ?? date('Y-m-d')) }}"></label>
                <label>College / Department<input name="college_department" class="form-control" required value="{{ old('college_department', $personal['college_department'] ?? optional($account->user->student)->college_department) }}"></label>
                @foreach(['last_name'=>'Last name','first_name'=>'First name','middle_name'=>'Middle name','suffix'=>'Suffix'] as $name=>$label)
                    <label>{{ $label }}<input name="{{ $name }}" class="form-control" {{ in_array($name,['last_name','first_name']) ? 'required' : '' }} value="{{ old($name, $personal[$name] ?? optional($account->user)->$name) }}"></label>
                @endforeach
                <label>Home address<input name="home_address" class="form-control" required value="{{ old('home_address', $personal['home_address'] ?? optional($account->user)->home_address) }}"></label>
                <label>Present address<input name="present_address" class="form-control" required value="{{ old('present_address', $personal['present_address'] ?? optional($account->user)->present_address) }}"></label>
                <label>Sex<select name="sex" class="form-control" required><option value="">Select</option>@foreach(['Female','Male','Other','Prefer not to say'] as $v)<option {{ old('sex',$personal['sex'] ?? optional($account->user)->gender)===$v?'selected':'' }}>{{ $v }}</option>@endforeach</select></label>
                <label>Birth date<input type="date" name="birth_date" class="form-control" required value="{{ old('birth_date', $personal['birth_date'] ?? optional(optional($account->user)->birthdate)->format('Y-m-d')) }}"></label>
                <label>Civil status<input name="civil_status" class="form-control" required value="{{ old('civil_status', $personal['civil_status'] ?? optional($account->user)->civil_status) }}"></label>
                <label>Mobile number<input name="mobile_number" class="form-control" required value="{{ old('mobile_number', $personal['mobile_number'] ?? optional($account->user)->phone_number) }}"></label>
                <label>Email<input type="email" name="email" class="form-control" required value="{{ old('email', $personal['email'] ?? optional($account->user)->email) }}"></label>
            </div>
        </section>
        <section class="assessment-step" data-step="1">
            <h2>Past Medical History</h2><p>Select all conditions that apply. Details are optional except for Other.</p>
            <div class="check-card-grid">
                @foreach($medicalConditions as $condition)
                <label class="check-card"><input type="checkbox" name="medical_conditions[]" value="{{ $condition }}" {{ in_array($condition,old('medical_conditions',$selectedMedical),true)?'checked':'' }}> <span>{{ $condition }}</span></label>
                @endforeach
            </div>
            <label>Other condition details<input name="other_medical_condition" class="form-control" value="{{ old('other_medical_condition') }}"></label>
            <div class="women-health">
                <h3>Women’s Health (when applicable)</h3>
                <div class="assessment-grid"><label>Last menstrual period<input type="date" name="last_menstrual_period" class="form-control" value="{{ old('last_menstrual_period',$women['last_menstrual_period'] ?? '') }}"></label>
                <label>Menstrual pattern<select name="menstrual_pattern" class="form-control"><option value="">Not applicable</option>@foreach(['Regular','Irregular','Prefer not to answer'] as $v)<option {{ old('menstrual_pattern',$women['menstrual_pattern'] ?? '')===$v?'selected':'' }}>{{ $v }}</option>@endforeach</select></label></div>
            </div>
        </section>
        <section class="assessment-step" data-step="2">
            <h2>Family and Social History</h2>
            <div class="assessment-history-block">
                <h3>Family History</h3>
                <p>Do you have a close relative (parent, grandparent, or sibling) diagnosed with any of the following?</p>
                <div class="check-card-grid">@foreach($familyConditions as $condition)<label class="check-card"><input type="checkbox" name="family_conditions[]" value="{{ $condition }}" {{ in_array($condition,old('family_conditions',$selectedFamily),true)?'checked':'' }}> <span>{{ $condition }}</span></label>@endforeach</div>
                <label>Other hereditary disease<input name="other_family_condition" class="form-control" value="{{ old('other_family_condition', $otherFamilyHistory) }}" placeholder="Please specify"></label>
            </div>
            <div class="assessment-history-block">
                <h3>Social History</h3>
                <div class="assessment-grid">
                <label>Do you smoke?<select name="smoking_status" class="form-control" required><option value="Never" {{ old('smoking_status',$social['smoking_status'] ?? 'Never')==='Never'?'selected':'' }}>No</option><option value="Current smoker" {{ old('smoking_status',$social['smoking_status'] ?? 'Never')==='Current smoker'?'selected':'' }}>Yes</option><option value="Former smoker" {{ old('smoking_status',$social['smoking_status'] ?? 'Never')==='Former smoker'?'selected':'' }}>Former smoker</option></select></label>
                <label>If yes, how many packs per day?<input type="number" name="smoking_packs" class="form-control" min="0" max="100" step="0.1" inputmode="decimal" value="{{ old('smoking_packs',$social['smoking_packs'] ?? '') }}" placeholder="Not applicable"></label>
                <label>Do you drink alcohol?<select name="drinks_alcohol" class="form-control" required>@foreach(['No','Yes'] as $v)<option {{ old('drinks_alcohol',$social['drinks_alcohol'] ?? 'No')===$v?'selected':'' }}>{{ $v }}</option>@endforeach</select></label>
                <label>Alcohol type<input name="alcohol_type" class="form-control" value="{{ old('alcohol_type',$social['alcohol_type'] ?? '') }}"></label>
                <label>How frequent?<select name="alcohol_frequency" class="form-control"><option value="">Not applicable</option>@foreach(['Occasional','Seldom'] as $v)<option {{ old('alcohol_frequency',$social['alcohol_frequency'] ?? '')===$v?'selected':'' }}>{{ $v }}</option>@endforeach</select></label>
                <label>Do you take medications at present?<select name="takes_medications" class="form-control" required data-takes-medications><option value="No" {{ old('takes_medications',$social['takes_medications'] ?? ($hasMedications ? 'Yes' : 'No'))==='No'?'selected':'' }}>No</option><option value="Yes" {{ old('takes_medications',$social['takes_medications'] ?? ($hasMedications ? 'Yes' : 'No'))==='Yes'?'selected':'' }}>Yes</option></select></label>
                </div>
                <div data-current-medications>
                    <h3>Current medications</h3>
                    <div data-medications>@forelse(optional($assessment)->medications ?: [] as $med)<input name="medications[]" class="form-control medication-row" value="{{ $med->medication }}" placeholder="Medication name">@empty<input name="medications[]" class="form-control medication-row" placeholder="Medication name">@endforelse</div>
                    <button type="button" class="btn btn-light" data-add-medication>Add medication</button>
                </div>
            </div>
        </section>
        <section class="assessment-step" data-step="3">
            <h2>Review and Submit</h2>
            <p>Please review your answers using Previous. Patient submission unlocks the portal; physical examination, vital signs, nursing interventions, and clinical recommendations are completed by authorized clinic staff.</p>
            <div class="alert alert-info">Submitting certifies that the information you provided is accurate to the best of your knowledge.</div>
        </section>
        <div class="assessment-actions">
            <button type="button" class="btn btn-light" data-previous disabled>Previous</button>
            <button type="submit" formnovalidate formaction="{{ route('patient.assessment.save') }}" class="btn btn-outline-primary">Save Draft</button>
            <button type="button" class="btn btn-primary" data-next>Next</button>
            <button type="submit" formaction="{{ route('patient.assessment.submit') }}" class="btn btn-success" data-submit hidden>Submit Assessment</button>
            <a href="{{ route('logout') }}" onclick="event.preventDefault();document.getElementById('logout-assessment').submit()" class="btn btn-link">Sign Out</a>
        </div>
    </form>
    <form id="logout-assessment" method="POST" action="{{ route('logout') }}">@csrf</form>
</div>
@endsection
@push('js')
<script>
(function(){var steps=[].slice.call(document.querySelectorAll('[data-step]')),current=0,prev=document.querySelector('[data-previous]'),next=document.querySelector('[data-next]'),submit=document.querySelector('[data-submit]');
function show(n){steps[current].classList.remove('active');current=n;steps[current].classList.add('active');prev.disabled=current===0;next.hidden=current===steps.length-1;submit.hidden=current!==steps.length-1;document.querySelectorAll('.assessment-progress span').forEach(function(x,i){x.classList.toggle('active',i<=current)});window.scrollTo({top:0,behavior:'smooth'});}
prev.onclick=function(){show(current-1)};next.onclick=function(){var invalid=steps[current].querySelector(':invalid');if(invalid){invalid.reportValidity();invalid.focus();return;}show(current+1)};
document.querySelector('[data-add-medication]').onclick=function(){var i=document.createElement('input');i.name='medications[]';i.className='form-control medication-row';document.querySelector('[data-medications]').appendChild(i);i.focus();};
var photo=document.querySelector('#formal_photo'),preview=document.querySelector('[data-formal-photo-preview]');photo.onchange=function(){if(photo.files&&photo.files[0])preview.src=URL.createObjectURL(photo.files[0]);};
var takes=document.querySelector('[data-takes-medications]'),medications=document.querySelector('[data-current-medications]');function syncMedications(){medications.hidden=takes.value!=='Yes';}takes.onchange=syncMedications;syncMedications();
@if($errors->any()) var first=document.querySelector(':invalid');if(first){var idx=steps.indexOf(first.closest('[data-step]'));if(idx>=0)show(idx);first.focus();}@endif
})();</script>
@endpush
