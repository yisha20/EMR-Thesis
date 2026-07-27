@extends('layouts.loginlayout')

@section('content')
<div class="login-card-modern">
    <div class="login-photo-strip">
        <img src="{{ asset('img/msuiit-clinic.png') }}" alt="MSU-IIT Clinic building">
        <span><img src="{{ asset('img/msuiit.png') }}" alt="MSU-IIT logo"> MSU-IIT Clinic</span>
    </div>

    <div class="login-card-body">
        <div class="login-card-header">
            <p class="eyebrow">Clinic EMR platform</p>
            <h1>Electronic Medical Record System</h1>
            <span>For MSU-IIT Clinic</span>
        </div>

        @if ($message = Session::get('success'))
            <div class="alert alert-success alert-block">
                <button type="button" class="close" data-dismiss="alert">&times;</button>
                <strong>{{ $message }}</strong>
            </div>
        @endif

        <form method="POST" action="{{ route('login') }}">
            @csrf

            <div class="form-group">
                <label for="account_type">Account Type</label>
                <select id="account_type" name="account_type" class="form-control @error('account_type') is-invalid @enderror" required>
                    <option value="">Select account type</option>
                    <option value="student" {{ old('account_type') === 'student' ? 'selected' : '' }}>Student</option>
                    <option value="faculty" {{ old('account_type') === 'faculty' ? 'selected' : '' }}>Faculty / Employee</option>
                    <option value="dependent" {{ old('account_type') === 'dependent' ? 'selected' : '' }}>Dependent</option>
                    <option value="staff" {{ old('account_type') === 'staff' ? 'selected' : '' }}>Clinic Staff</option>
                </select>
                @error('account_type')<span class="invalid-feedback d-block"><strong>{{ $message }}</strong></span>@enderror
            </div>

            <div class="form-group">
                <label for="email">{{ __('Email Address') }}</label>
                <div class="input-icon">
                    <i class="fa fa-envelope-o"></i>
                    <input id="email" type="email" class="form-control @error('email') is-invalid @enderror" name="email" value="{{ old('email') }}" required autocomplete="email" autofocus>
                </div>

                @error('email')
                    <span class="invalid-feedback d-block" role="alert">
                        <strong>{{ $message }}</strong>
                    </span>
                @enderror
            </div>

            <div class="form-group">
                <label for="password">{{ __('Password') }}</label>
                <div class="input-icon password-field">
                    <i class="fa fa-lock"></i>
                    <input id="password" type="password" class="form-control @error('password') is-invalid @enderror" name="password" required autocomplete="current-password">
                    <button type="button" class="password-toggle" data-password-toggle="password" aria-label="Show password" aria-pressed="false">
                        <i class="fa fa-eye"></i>
                    </button>
                </div>

                @error('password')
                    <span class="invalid-feedback d-block" role="alert">
                        <strong>{{ $message }}</strong>
                    </span>
                @enderror
            </div>

            <div class="form-group login-options">
                <div class="form-check">
                    <input class="form-check-input" type="checkbox" name="remember" id="remember" {{ old('remember') ? 'checked' : '' }}>
                    <label class="form-check-label" for="remember">{{ __('Remember Me') }}</label>
                </div>
            </div>

            <button type="submit" class="btn btn-primary btn-block login-submit">
                {{ __('Login') }}
            </button>
            <a href="{{ route('student.register') }}" class="student-auth-link">Create a patient portal account</a>
        </form>
    </div>
</div>
@endsection

@push('scripts')
<script>
(function () {
    var toggle = document.querySelector('[data-password-toggle="password"]');
    var password = document.getElementById('password');

    if (!toggle || !password) {
        return;
    }

    toggle.addEventListener('click', function () {
        var show = password.type === 'password';
        password.type = show ? 'text' : 'password';
        toggle.setAttribute('aria-label', show ? 'Hide password' : 'Show password');
        toggle.setAttribute('aria-pressed', show ? 'true' : 'false');
        toggle.querySelector('i').classList.toggle('fa-eye', !show);
        toggle.querySelector('i').classList.toggle('fa-eye-slash', show);
    });
})();
</script>
@endpush
