@extends('layouts.loginlayout')

@section('content')
<div class="login-card-modern">
    <div class="login-card-body">
        <div class="login-card-header">
            <p class="eyebrow">Account recovery</p>
            <h1>Create New Password</h1>
            <span>Use the secure link sent to your email.</span>
        </div>

        <form method="POST" action="{{ route('password.update') }}">
            @csrf
            <input type="hidden" name="token" value="{{ $token }}">

            <div class="form-group">
                <label for="email">{{ __('Email Address') }}</label>
                <div class="input-icon">
                    <i class="fa fa-envelope-o"></i>
                    <input id="email" type="email" class="form-control @error('email') is-invalid @enderror" name="email" value="{{ $email ?? old('email') }}" required autocomplete="email">
                </div>
                @error('email')<span class="invalid-feedback d-block"><strong>{{ $message }}</strong></span>@enderror
            </div>

            <div class="form-group">
                <label for="password">{{ __('Password') }}</label>
                <div class="input-icon">
                    <i class="fa fa-lock"></i>
                    <input id="password" type="password" class="form-control @error('password') is-invalid @enderror" name="password" required autocomplete="new-password">
                </div>
                @error('password')<span class="invalid-feedback d-block"><strong>{{ $message }}</strong></span>@enderror
            </div>

            <div class="form-group">
                <label for="password-confirm">{{ __('Confirm Password') }}</label>
                <div class="input-icon">
                    <i class="fa fa-lock"></i>
                    <input id="password-confirm" type="password" class="form-control" name="password_confirmation" required autocomplete="new-password">
                </div>
            </div>

            <button type="submit" class="btn btn-primary btn-block login-submit">{{ __('Reset Password') }}</button>
        </form>
    </div>
</div>
@endsection
