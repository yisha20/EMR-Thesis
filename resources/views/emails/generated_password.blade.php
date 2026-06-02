@component('mail::message')
# Generated Password

Hi {{ $user->first_name }},

We generated a password for you, use this to login and then change your password in the system. 

<b>{{ $generatedPassword }}</b>

Thanks,<br>
EMR
@endcomponent
