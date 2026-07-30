<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}" class="{{ request()->routeIs('student.register', 'patient.register.type') ? 'student-registration-page' : '' }}">
    <head>
        <meta charset="utf-8">
        <meta name="viewport" content="width=device-width, initial-scale=1">
        <link rel="dns-prefetch" href="//fonts.gstatic.com">
        <link href="https://fonts.googleapis.com/css?family=Inter:400,500,600,700,800|Nunito:400,600,700&display=swap" rel="stylesheet">
        <link rel="stylesheet" href="{{asset('css/app.css')}}">
        <link rel="stylesheet" href="{{ asset('css/side.css') }}?v={{ filemtime(public_path('css/side.css')) }}">
        <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/4.7.0/css/font-awesome.min.css">
        <title>{{config('app.name', 'EMRSYSTEM')}}</title>

    </head>
    <body class="emr-login-page {{ request()->routeIs('student.register', 'patient.register.type') ? 'student-registration-page' : '' }}">
        <div class ="container-fluid login-container">
           @yield('content')
        </div>
        @stack('scripts')
    </body>
</html>
