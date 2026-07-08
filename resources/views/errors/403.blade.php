<!doctype html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Access Restricted | {{ config('app.name', 'EMR') }}</title>
    <link href="{{ asset('css/app.css') }}" rel="stylesheet">
    <link href="{{ asset('css/side.css') }}" rel="stylesheet">
</head>
<body class="emr-error-page">
    <main class="emr-error-card">
        <span class="emr-error-code">403</span>
        <h1>Access restricted</h1>
        <p>{{ $exception->getMessage() ?: 'You do not have permission to access this EMR page.' }}</p>
        <a href="{{ url('/') }}">Return to EMR</a>
    </main>
</body>
</html>
