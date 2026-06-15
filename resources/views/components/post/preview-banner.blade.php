@props(['backUrl' => null])

<div class="alert alert-warning border-warning mb-4 d-flex flex-wrap align-items-center justify-content-between gap-2"
    role="status">
    <span>{{ __('posts.preview.banner') }}</span>
    @if ($backUrl)
        <a href="{{ $backUrl }}" class="btn btn-sm btn-outline-dark">{{ __('posts.preview.back_to_edit') }}</a>
    @endif
</div>
