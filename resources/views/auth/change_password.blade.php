@extends('layouts.loginlayout')

@section('content')
<main class="portal-auth-shell portal-auth-compact">
    <div class="portal-brand"><img src="{{ asset('img/msu-iit-logo.png') }}" alt="MSU-IIT seal"><div><strong>MSU-IIT Clinic</strong><span>Electronic Medical Record System</span></div></div>
    <section class="login-card-modern">
        <div class="login-card-body">
            <div class="login-card-header"><p class="eyebrow">Staff account security</p><h1>Change Temporary Password</h1><span>Create your private permanent password before continuing.</span></div>
            <form method="POST" action="{{ route('password.change.update') }}">
                @csrf
                <div class="form-group"><label for="password">New Password</label><input id="password" type="password" name="password" class="form-control @error('password') is-invalid @enderror" required autocomplete="new-password">@error('password')<span class="invalid-feedback">{{ $message }}</span>@enderror</div>
                <div class="form-group"><label for="password-confirm">Confirm New Password</label><input id="password-confirm" type="password" name="password_confirmation" class="form-control" required autocomplete="new-password"></div>
                <p class="password-guidance">Use at least 8 characters. Your temporary credential stops working after this change.</p>
                <button type="submit" class="btn btn-primary btn-block">Set Permanent Password</button>
            </form>
            <form method="POST" action="{{ route('logout') }}">@csrf<button type="submit" class="btn btn-link student-auth-link">Logout</button></form>
        </div>
    </section>
</main>
@endsection
