<!doctype html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Page Not Found | {{ config('app.name', 'EMR') }}</title>
    <link href="{{ asset('css/app.css') }}" rel="stylesheet">
    <link href="{{ asset('css/side.css') }}" rel="stylesheet">
</head>
<body class="emr-error-page">
    <main class="emr-error-card">
        <span class="emr-error-code">404</span>
        <h1>Page not found</h1>
        <p>The clinic record or page you are looking for is unavailable or has moved.</p>
        <a href="{{ url('/') }}">Return to EMR</a>
    </main>
</body>
</html>
