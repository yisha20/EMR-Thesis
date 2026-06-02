@component('mail::message')
# Forgot Password

Hi {{ $user->first_name }},

If you requested for a password reset, please confirm. Otherwise ignore this message.

@component('mail::button', ['url' => route('auth.verify', $user->email)])
Confirm Password Reset
@endcomponent

Thanks,<br>
EMR
@endcomponent
