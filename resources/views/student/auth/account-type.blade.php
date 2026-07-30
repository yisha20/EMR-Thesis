@extends('layouts.loginlayout')

@section('content')
<main class="portal-auth-shell">
    <div class="portal-brand">
        <img src="{{ asset('img/msu-iit-logo.png') }}" alt="MSU-IIT seal">
        <div><strong>MSU-IIT Clinic</strong><span>Electronic Medical Record System</span></div>
    </div>
    <section class="login-card-modern account-type-card" aria-labelledby="registration-title">
        <div class="login-card-body">
            <div class="login-card-header">
                <p class="eyebrow">Patient portal registration</p>
                <h1 id="registration-title">Create Your Patient Portal Account</h1>
                <span>What type of account are you registering?</span>
            </div>
            <form id="account-type-form" action="{{ route('student.register') }}" method="GET">
                <fieldset class="account-type-grid">
                    <legend class="sr-only">Choose an account type</legend>
                    @foreach ([
                        'student' => ['fa-graduation-cap', 'Student', 'MSU-IIT enrolled student', 'Use your MSU-IIT Student ID.'],
                        'faculty' => ['fa-id-badge', 'Faculty / Employee', 'Faculty member or authorized university employee', 'Use your MSU-IIT Faculty or Employee ID.'],
                        'dependent' => ['fa-users', 'Dependent', 'Valid family dependent of a registered Student or Faculty member', 'Must be linked to a valid sponsor.'],
                    ] as $value => $option)
                        <label class="account-type-option">
                            <input type="radio" name="type" value="{{ $value }}" required>
                            <span class="account-type-option-body">
                                <i class="fa {{ $option[0] }}" aria-hidden="true"></i>
                                <strong>{{ $option[1] }}</strong>
                                <span>{{ $option[2] }}</span>
                                <small>{{ $option[3] }}</small>
                            </span>
                        </label>
                    @endforeach
                </fieldset>
                <button class="btn btn-primary btn-block" type="submit">Continue</button>
                <a class="student-auth-link" href="{{ route('login') }}">Back to Login</a>
            </form>
        </div>
    </section>
</main>
@endsection
