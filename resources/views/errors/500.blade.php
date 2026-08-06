<!doctype html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>System Error | {{ config('app.name', 'EMR') }}</title>
    <link href="{{ asset('css/app.css') }}" rel="stylesheet">
    <link href="{{ asset('css/side.css') }}" rel="stylesheet">
</head>
<body class="emr-error-page">
    <main class="emr-error-card">
        <span class="emr-error-code">500</span>
        <h1>Something went wrong</h1>
        <p>The EMR system could not complete the request. Please try again or contact the clinic system administrator.</p>
        @if (!empty($errorReference))
            <p><strong>Error Reference:</strong> {{ $errorReference }}</p>
        @endif
        <a href="{{ url('/') }}">Return to EMR</a>
    </main>
</body>
</html>
