@props(['seo'])

@php
    $ogLocale = match (app()->getLocale()) {
        'ru' => 'ru_RU',
        'en' => 'en_US',
        default => str_replace('-', '_', app()->getLocale()) . '_' . strtoupper(app()->getLocale()),
    };
@endphp

<meta name="description" content="{{ $seo->description }}">
<meta name="robots" content="{{ $seo->robots }}">
<link rel="canonical" href="{{ $seo->canonicalUrl }}">

<meta property="og:type" content="{{ $seo->ogType }}">
<meta property="og:locale" content="{{ $ogLocale }}">
<meta property="og:title" content="{{ $seo->title }}">
<meta property="og:description" content="{{ $seo->description }}">
<meta property="og:url" content="{{ $seo->pageUrl }}">
@if ($seo->ogImageUrl)
    <meta property="og:image" content="{{ $seo->ogImageUrl }}">
@endif

<meta name="twitter:card" content="{{ $seo->ogImageUrl ? 'summary_large_image' : 'summary' }}">
<meta name="twitter:title" content="{{ $seo->title }}">
<meta name="twitter:description" content="{{ $seo->description }}">
@if ($seo->ogImageUrl)
    <meta name="twitter:image" content="{{ $seo->ogImageUrl }}">
@endif
