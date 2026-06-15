<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">

<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="theme-color" content="#f8f9fa">
    <title>{{ __('maintenance.title') }}</title>
    <link rel="stylesheet" href="{{ asset('css/maintenance.css') }}">
</head>

<body>
    <main class="card">
        <h1>{{ __('maintenance.heading') }}</h1>
        <p>{{ __('maintenance.message') }}</p>
    </main>
</body>

</html>
