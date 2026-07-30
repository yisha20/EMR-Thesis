@extends('layouts.loginlayout')

@section('content')
<main class="portal-auth-shell portal-auth-compact">
    <div class="portal-brand"><img src="{{ asset('img/msu-iit-logo.png') }}" alt="MSU-IIT seal"><div><strong>MSU-IIT Clinic</strong><span>Electronic Medical Record System</span></div></div>
    <section class="login-card-modern">
        <div class="login-card-body">
            <div class="login-card-header"><p class="eyebrow">Account recovery</p><h1>Create New Password</h1><span>Choose a secure password for {{ isset($email) ? preg_replace('/(^.).*(@.*$)/', '$1••••$2', $email) : 'your account' }}.</span></div>
            <form method="POST" action="{{ route('password.update') }}">
                @csrf
                <input type="hidden" name="token" value="{{ $token }}">
                <input type="hidden" name="email" value="{{ $email ?? old('email') }}">
                @error('email')<div class="alert alert-danger">{{ $message }}</div>@enderror
                @foreach ([['password', 'New Password'], ['password_confirmation', 'Confirm New Password']] as $field)
                    <div class="form-group">
                        <label for="{{ $field[0] }}">{{ $field[1] }}</label>
                        <div class="password-field">
                            <input id="{{ $field[0] }}" type="password" name="{{ $field[0] }}" class="form-control {{ $field[0] === 'password' && $errors->has('password') ? 'is-invalid' : '' }}" required autocomplete="new-password">
                            <button type="button" class="password-toggle" data-password-toggle="{{ $field[0] }}" aria-label="Show {{ strtolower($field[1]) }}"><i class="fa fa-eye"></i></button>
                        </div>
                        @if($field[0] === 'password')@error('password')<span class="invalid-feedback d-block">{{ $message }}</span>@enderror @endif
                    </div>
                @endforeach
                <p class="password-guidance">Minimum 8 characters. Use upper- and lowercase letters, a number, and a symbol for a stronger password.</p>
                <button type="submit" class="btn btn-primary btn-block">Reset Password</button>
                <a href="{{ route('login') }}" class="student-auth-link">Back to Login</a>
            </form>
        </div>
    </section>
</main>
@endsection

@push('scripts')
<script>
document.querySelectorAll('[data-password-toggle]').forEach(function (button) {
    button.addEventListener('click', function () {
        var input = document.getElementById(button.getAttribute('data-password-toggle'));
        var show = input.type === 'password';
        input.type = show ? 'text' : 'password';
        button.setAttribute('aria-label', show ? 'Hide password' : 'Show password');
        button.querySelector('i').className = show ? 'fa fa-eye-slash' : 'fa fa-eye';
    });
});
</script>
@endpush
