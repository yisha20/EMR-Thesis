@extends('layouts.loginlayout')

@section('content')
<main class="portal-auth-shell portal-auth-compact">
    <div class="portal-brand">
        <img src="{{ asset('img/msu-iit-logo.png') }}" alt="MSU-IIT seal">
        <div><strong>MSU-IIT Clinic</strong><span>Electronic Medical Record System</span></div>
    </div>
    <section class="login-card-modern">
        <div class="login-card-body">
            <div class="login-card-header">
                <p class="eyebrow">Secure account recovery</p>
                <h1>Forgot Your Password?</h1>
                <span>Enter the email address connected to your EMR account. We will send instructions for resetting your password.</span>
            </div>
            @if (session('status'))<div class="alert alert-success" role="status">{{ session('status') }}</div>@endif
            <form method="POST" action="{{ route('password.email') }}">
                @csrf
                <div class="form-group">
                    <label for="email">Email Address</label>
                    <input id="email" type="email" name="email" value="{{ old('email') }}" class="form-control @error('email') is-invalid @enderror" required autocomplete="email" autofocus>
                    @error('email')<span class="invalid-feedback">{{ $message }}</span>@enderror
                </div>
                <button type="submit" class="btn btn-primary btn-block">Send Password Reset Link</button>
                <a href="{{ route('login') }}" class="student-auth-link">Back to Login</a>
            </form>
            <p class="portal-help-note">Cannot access your registered staff email? Contact an authorized Clinic Administrator or ICTC representative for identity verification.</p>
        </div>
    </section>
</main>
@endsection
