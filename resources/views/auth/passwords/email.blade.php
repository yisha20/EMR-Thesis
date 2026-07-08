@extends('layouts.loginlayout')

@section('content')
<div class="login-card-modern">
    <div class="login-card-body">
        <div class="login-card-header">
            <p class="eyebrow">Account recovery</p>
            <h1>Reset Password</h1>
            <span>Enter your email to receive a secure reset link.</span>
        </div>

        @if (session('status'))
            <div class="alert alert-success">{{ session('status') }}</div>
        @endif

        <form method="POST" action="{{ route('password.email') }}">
            @csrf
            <div class="form-group">
                <label for="email">{{ __('Email Address') }}</label>
                <div class="input-icon">
                    <i class="fa fa-envelope-o"></i>
                    <input id="email" type="email" class="form-control @error('email') is-invalid @enderror" name="email" value="{{ old('email') }}" required autocomplete="email" autofocus>
                </div>
                @error('email')<span class="invalid-feedback d-block"><strong>{{ $message }}</strong></span>@enderror
            </div>

            <button type="submit" class="btn btn-primary btn-block login-submit">{{ __('Send Password Reset Link') }}</button>
            <a href="{{ route('login') }}" class="student-auth-link">Return to login</a>
        </form>
    </div>
</div>
@endsection
