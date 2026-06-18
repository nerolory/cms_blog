@props(['engagement'])

@php($viewsLabel = __('engagement.views', ['count' => number_format($engagement->viewsCount)]))

<div {{ $attributes->merge(['class' => 'post-card__stats d-flex flex-wrap gap-2']) }}>
    <span class="badge border post-card__views d-inline-flex align-items-center"
        title="{{ $viewsLabel }}" aria-label="{{ $viewsLabel }}">
        <svg class="post-card__views-icon" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 16 16" fill="currentColor"
            aria-hidden="true">
            <path
                d="M16 8s-3-5.5-8-5.5S0 8 0 8s3 5.5 8 5.5S16 8 16 8M1.173 8a13 13 0 0 1 1.66-2.043C4.12 4.668 5.88 3.5 8 3.5s3.879 1.168 5.168 2.457A13 13 0 0 1 14.828 8q-.086.13-.195.288c-.335.48-.83 1.12-1.465 1.755C11.879 11.332 10.119 12.5 8 12.5s-3.879-1.168-5.168-2.457A13 13 0 0 1 1.172 8z" />
            <path d="M8 5.5a2.5 2.5 0 1 0 0 5 2.5 2.5 0 0 0 0-5M6.5 8a1.5 1.5 0 1 1 3 0 1.5 1.5 0 0 1-3 0" />
        </svg>
        <span class="post-card__views-count">{{ number_format($engagement->viewsCount) }}</span>
    </span>
    <x-post.reaction-badges :reaction-counts="$engagement->reactionCounts" class="gap-1" />
</div>
