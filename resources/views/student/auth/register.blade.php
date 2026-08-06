@extends('layouts.loginlayout')

@section('content')
@php
    $titles = ['student' => 'Student', 'faculty' => 'Faculty / Employee', 'dependent' => 'Dependent'];
    $isDependent = $accountType === 'dependent';
@endphp
<main class="portal-auth-shell">
    <div class="portal-brand">
        <img src="{{ asset('img/msu-iit-logo.png') }}" alt="MSU-IIT seal">
        <div><strong>MSU-IIT Clinic</strong><span>Electronic Medical Record System</span></div>
    </div>
    <section class="login-card-modern student-register-card">
        <div class="login-card-body">
            <div class="login-card-header">
                <p class="eyebrow">{{ $titles[$accountType] }} registration</p>
                <h1>Create Your Patient Portal Account</h1>
                <span>Complete each step. Your details remain available when you move backward.</span>
            </div>

            <ol class="registration-progress" aria-label="Registration progress">
                @foreach (['Personal', $isDependent ? 'Sponsor' : ($accountType === 'student' ? 'Academic' : 'Employment'), 'Contact', 'Security', 'Review'] as $index => $step)
                    <li class="{{ $index === 0 ? 'active' : '' }}" data-progress-step="{{ $index }}"><span>{{ $index + 1 }}</span>{{ $step }}</li>
                @endforeach
            </ol>

            @error('registration')<div class="alert alert-danger" role="alert">{{ $message }}</div>@enderror
            <form method="POST" action="{{ route('student.register.store') }}" class="student-register-form" data-multi-step novalidate>
                @csrf
                <input type="hidden" name="account_type" value="{{ $accountType }}">

                <section class="registration-step active" data-step="0">
                    <h2>Personal Information</h2>
                    <div class="student-register-grid">
                        @include('student.auth.partials.field', ['name' => 'first_name', 'label' => 'First Name', 'required' => true])
                        @include('student.auth.partials.field', ['name' => 'middle_name', 'label' => 'Middle Name'])
                        @include('student.auth.partials.field', ['name' => 'last_name', 'label' => 'Last Name', 'required' => true])
                        @include('student.auth.partials.field', ['name' => 'suffix', 'label' => 'Suffix'])
                        <div class="form-group">
                            <label for="birth_date">Birth Date <span aria-hidden="true">*</span></label>
                            <input id="birth_date" type="date" name="birth_date" value="{{ old('birth_date') }}" class="form-control @error('birth_date') is-invalid @enderror" required>
                            @error('birth_date')<span class="invalid-feedback">{{ $message }}</span>@enderror
                        </div>
                        <div class="form-group">
                            <label for="age">Age</label>
                            <input id="age" class="form-control" value="" readonly aria-describedby="age-help">
                            <small id="age-help">Calculated from birth date</small>
                        </div>
                        <div class="form-group">
                            <label for="gender">Sex / Gender <span aria-hidden="true">*</span></label>
                            <select id="gender" name="gender" class="form-control @error('gender') is-invalid @enderror" required>
                                <option value="">Select</option>
                                @foreach (['Male', 'Female'] as $value)<option value="{{ $value }}" {{ old('gender') === $value ? 'selected' : '' }}>{{ $value }}</option>@endforeach
                            </select>
                            @error('gender')<span class="invalid-feedback">{{ $message }}</span>@enderror
                        </div>
                        <div class="form-group">
                            <label for="civil_status">Civil Status <span aria-hidden="true">*</span></label>
                            <select id="civil_status" name="civil_status" class="form-control @error('civil_status') is-invalid @enderror" required>
                                <option value="">Select</option>
                                @foreach (['Single', 'Married', 'Widowed', 'Separated'] as $value)<option value="{{ $value }}" {{ old('civil_status') === $value ? 'selected' : '' }}>{{ $value }}</option>@endforeach
                            </select>
                            @error('civil_status')<span class="invalid-feedback">{{ $message }}</span>@enderror
                        </div>
                    </div>
                </section>

                <section class="registration-step" data-step="1">
                    <h2>{{ $isDependent ? 'Sponsor Information' : ($accountType === 'student' ? 'Academic Information' : 'Employment Information') }}</h2>
                    <div class="student-register-grid">
                        @if ($accountType === 'student')
                            @include('student.auth.partials.field', ['name' => 'student_id_number', 'label' => 'Student ID Number', 'required' => true])
                            @include('student.auth.partials.field', ['name' => 'college_department', 'label' => 'College / Department', 'required' => true])
                            @include('student.auth.partials.field', ['name' => 'program', 'label' => 'Program'])
                            @include('student.auth.partials.field', ['name' => 'year_level', 'label' => 'Year Level'])
                        @elseif ($accountType === 'faculty')
                            @include('student.auth.partials.field', ['name' => 'faculty_id_number', 'label' => 'Faculty / Employee ID Number', 'required' => true])
                            @include('student.auth.partials.field', ['name' => 'college_department', 'label' => 'College / Office / Department', 'required' => true])
                            @include('student.auth.partials.field', ['name' => 'position_designation', 'label' => 'Position / Designation', 'required' => true])
                            @include('student.auth.partials.field', ['name' => 'employment_type', 'label' => 'Employment Type'])
                        @else
                            <div class="alert alert-info full-width">Dependents must be sponsored by a registered MSU-IIT Faculty or Employee account.</div>
                            <div class="form-group">
                                <label for="sponsor_type">Sponsor Type <span aria-hidden="true">*</span></label>
                                <select id="sponsor_type" name="sponsor_type" class="form-control @error('sponsor_type') is-invalid @enderror" required>
                                    <option value="">Select</option>
                                    <option value="faculty" {{ old('sponsor_type') === 'faculty' ? 'selected' : '' }}>Faculty</option>
                                </select>
                                @error('sponsor_type')<span class="invalid-feedback">{{ $message }}</span>@enderror
                            </div>
                            @include('student.auth.partials.field', ['name' => 'sponsor_id_number', 'label' => 'Sponsor ID Number', 'required' => true])
                            @include('student.auth.partials.field', ['name' => 'sponsor_email', 'label' => 'Sponsor Email', 'type' => 'email', 'required' => true])
                            <div class="form-group">
                                <label for="dependent_relationship">Relationship to Sponsor <span aria-hidden="true">*</span></label>
                                <select id="dependent_relationship" name="dependent_relationship" class="form-control @error('dependent_relationship') is-invalid @enderror" required>
                                    <option value="">Select</option>
                                    @foreach ($relationships as $value)<option value="{{ $value }}" {{ old('dependent_relationship') === $value ? 'selected' : '' }}>{{ $value }}</option>@endforeach
                                </select>
                                @error('dependent_relationship')<span class="invalid-feedback">{{ $message }}</span>@enderror
                            </div>
                            @include('student.auth.partials.field', ['name' => 'dependent_relationship_details', 'label' => 'Relationship Details'])
                        @endif
                    </div>
                </section>

                <section class="registration-step" data-step="2">
                    <h2>Contact and Address</h2>
                    <div class="student-register-grid">
                        @include('student.auth.partials.field', ['name' => 'contact_number', 'label' => 'Contact Number', 'required' => !$isDependent])
                        @include('student.auth.partials.field', ['name' => 'home_address', 'label' => 'Home Address', 'required' => true])
                        @include('student.auth.partials.field', ['name' => 'present_address', 'label' => 'Present Address', 'required' => true])
                    </div>
                </section>

                <section class="registration-step" data-step="3">
                    <h2>Account Security</h2>
                    <div class="student-register-grid">
                        @include('student.auth.partials.field', ['name' => 'email', 'label' => 'Email Address', 'type' => 'email', 'required' => true, 'autocomplete' => 'email'])
                        @include('student.auth.partials.field', ['name' => 'password', 'label' => 'Password', 'type' => 'password', 'required' => true, 'autocomplete' => 'new-password'])
                        @include('student.auth.partials.field', ['name' => 'password_confirmation', 'label' => 'Confirm Password', 'type' => 'password', 'required' => true, 'autocomplete' => 'new-password'])
                    </div>
                    <p class="password-guidance">Use at least 8 characters. A longer mix of letters, numbers, and symbols is recommended.</p>
                    @if ($isDependent)
                        <label class="verification-consent"><input type="checkbox" name="verification_consent" value="1" required {{ old('verification_consent') ? 'checked' : '' }}> I consent to verification of the sponsor relationship. <span aria-hidden="true">*</span></label>
                        @error('verification_consent')<span class="invalid-feedback d-block">{{ $message }}</span>@enderror
                    @endif
                </section>

                <section class="registration-step" data-step="4">
                    <h2>Review and Submit</h2>
                    <p>Review your information using Back. Your account type is <strong>{{ $titles[$accountType] }}</strong>.</p>
                    @if ($isDependent)<p>Your account will remain pending until clinic staff verify the sponsor relationship.</p>@endif
                    <p>After registration, sign in and complete the required Health Assessment.</p>
                </section>

                <div class="registration-actions">
                    <button type="button" class="btn btn-light" data-step-back>Back</button>
                    <button type="button" class="btn btn-primary" data-step-next>Next</button>
                    <button type="submit" class="btn btn-primary" data-step-submit>Create Account</button>
                </div>
                <a href="{{ route('patient.register.type') }}" class="student-auth-link">Change Account Type</a>
            </form>
        </div>
    </section>
</main>
@endsection

@push('scripts')
<script>
(function () {
    var form = document.querySelector('[data-multi-step]');
    if (!form) return;
    var steps = [].slice.call(form.querySelectorAll('[data-step]'));
    var progress = [].slice.call(document.querySelectorAll('[data-progress-step]'));
    var back = form.querySelector('[data-step-back]');
    var next = form.querySelector('[data-step-next]');
    var submit = form.querySelector('[data-step-submit]');
    var current = {{ $errors->any() ? '0' : '0' }};

    function show(index) {
        current = Math.max(0, Math.min(index, steps.length - 1));
        steps.forEach(function (step, i) { step.classList.toggle('active', i === current); });
        progress.forEach(function (item, i) {
            item.classList.toggle('active', i === current);
            item.classList.toggle('complete', i < current);
        });
        back.hidden = current === 0;
        next.hidden = current === steps.length - 1;
        submit.hidden = current !== steps.length - 1;
        steps[current].querySelector('input, select, textarea')?.focus();
    }
    next.addEventListener('click', function () {
        var invalid = steps[current].querySelector(':invalid');
        if (invalid) { invalid.reportValidity(); invalid.focus(); return; }
        show(current + 1);
    });
    back.addEventListener('click', function () { show(current - 1); });
    var birthDate = document.getElementById('birth_date');
    var age = document.getElementById('age');
    function calculateAge() {
        if (!birthDate.value) { age.value = ''; return; }
        var birth = new Date(birthDate.value + 'T00:00:00');
        var today = new Date();
        var years = today.getFullYear() - birth.getFullYear();
        if (today < new Date(today.getFullYear(), birth.getMonth(), birth.getDate())) years--;
        age.value = Math.max(0, years);
    }
    birthDate.addEventListener('change', calculateAge);
    calculateAge();
    show(current);
    var firstError = form.querySelector('.is-invalid');
    if (firstError) {
        var errorStep = steps.findIndex(function (step) { return step.contains(firstError); });
        show(errorStep < 0 ? 0 : errorStep);
        firstError.focus();
    }
})();
</script>
@endpush
