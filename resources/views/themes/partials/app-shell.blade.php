@php
    use App\Support\Cache\CacheVersionManager;
    use App\Support\Theme;
    use App\Services\Contracts\SiteSettingsServiceContract;

    $theme = Theme::current();
    $bsTheme = $theme->bootstrapTheme();
    $bodyClass = trim('min-vh-100 d-flex flex-column ' . $theme->bodyClass());
    $cacheVersion = app(CacheVersionManager::class)->current();
    $siteName = app(SiteSettingsServiceContract::class)->siteName();
@endphp
<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}"
    @if ($bsTheme) data-bs-theme="{{ $bsTheme }}" @endif>

<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="theme-color" content="#0d6efd">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <meta name="app-cache-version" content="{{ $cacheVersion }}">
    <title>@yield('title', $siteName) — {{ $siteName }}</title>
    @stack('meta')
    @stack('head-jsonld')
    @if (file_exists(public_path('build/manifest.json')) || file_exists(public_path('hot')))
        @vite(['resources/css/app.css', 'resources/js/app.js'])
    @endif
    @stack('styles')
</head>

<body class="{{ $bodyClass }}">
    <x-layout.navbar />

    <main class="container flex-grow-1 py-4">
        @if (session('success'))
            <x-ui.alert type="success" :message="session('success')" class="mb-4" />
        @endif

        @yield('content')
    </main>

    @stack('scripts')
</body>

</html>
