@props(['seo'])

@php
    $shareUrl = urlencode($seo->pageUrl);
    $shareTitle = urlencode($seo->title);
@endphp

<div class="d-flex flex-wrap gap-2 align-items-center">
    <span class="small text-muted me-1">{{ __('engagement.share.label') }}:</span>
    <a href="https://vk.com/share.php?url={{ $shareUrl }}&title={{ $shareTitle }}"
        class="btn btn-sm btn-outline-secondary" target="_blank" rel="noopener noreferrer">VK</a>
    <a href="https://www.facebook.com/sharer/sharer.php?u={{ $shareUrl }}" class="btn btn-sm btn-outline-secondary"
        target="_blank" rel="noopener noreferrer">Facebook</a>
    <a href="https://twitter.com/intent/tweet?url={{ $shareUrl }}&text={{ $shareTitle }}"
        class="btn btn-sm btn-outline-secondary" target="_blank" rel="noopener noreferrer">X</a>
</div>
