@props([
    'post' => null,
])

@inject('postPresenterFactory', App\Presenters\PostPresenterFactory::class)

@php
    use App\Support\PostTheme;
    $postPresenter = $post !== null ? $postPresenterFactory->for($post) : null;
    $themeEnabled = (bool) old('use_article_theme', $postPresenter?->hasCustomTheme() ?? false);
@endphp

<div class="card border-0 bg-body-tertiary mb-3 post-theme-fields" data-post-theme-fields>
    <div class="card-body">
        <h2 class="h6 mb-3">{{ __('posts.fields.theme_section') }}</h2>

        <div class="form-check mb-3">
            <input type="checkbox" class="form-check-input" name="use_article_theme" id="use_article_theme" value="1"
                data-theme-toggle @checked($themeEnabled)>
            <label class="form-check-label" for="use_article_theme">
                {{ __('posts.fields.use_article_theme') }}
            </label>
        </div>

        <div data-theme-settings @class(['d-none' => !$themeEnabled])>
            <div class="row g-3">
                <div class="col-md-6">
                    <label for="theme_primary_color"
                        class="form-label">{{ __('posts.fields.theme_primary_color') }}</label>
                    <input type="color" name="theme_primary_color" id="theme_primary_color"
                        class="form-control form-control-color w-100"
                        value="{{ old('theme_primary_color', $post?->theme_primary_color ?? '#0d6efd') }}"
                        data-theme-input="primary">
                </div>
                <div class="col-md-6">
                    <label for="theme_accent_color"
                        class="form-label">{{ __('posts.fields.theme_accent_color') }}</label>
                    <input type="color" name="theme_accent_color" id="theme_accent_color"
                        class="form-control form-control-color w-100"
                        value="{{ old('theme_accent_color', $post?->theme_accent_color ?? '#6c757d') }}"
                        data-theme-input="accent">
                </div>
                <div class="col-12">
                    <label for="content_opacity" class="form-label">
                        {{ __('posts.fields.content_opacity') }}
                        <span class="text-muted" data-content-opacity-value>
                            {{ old('content_opacity', $post !== null ? PostTheme::normalizeOpacity($post->content_opacity) : PostTheme::MAX_CONTENT_OPACITY) }}%
                        </span>
                    </label>
                    <input type="range" name="content_opacity" id="content_opacity" class="form-range"
                        min="{{ PostTheme::MIN_CONTENT_OPACITY }}" max="{{ PostTheme::MAX_CONTENT_OPACITY }}"
                        step="1"
                        value="{{ old('content_opacity', $post !== null ? PostTheme::normalizeOpacity($post->content_opacity) : PostTheme::MAX_CONTENT_OPACITY) }}"
                        data-theme-input="opacity">
                    <div class="form-text">{{ __('posts.fields.content_opacity_hint') }}</div>
                </div>
            </div>

            <div class="post-theme-preview mt-3 rounded border p-3" data-theme-preview>
                <div class="post-theme-preview__panel rounded p-3" data-theme-preview-panel>
                    <p class="mb-1 fw-semibold" data-theme-preview-title>{{ __('posts.fields.theme_preview_title') }}
                    </p>
                    <p class="mb-0 small text-muted">{{ __('posts.fields.theme_preview_text') }}</p>
                </div>
            </div>
        </div>
    </div>
</div>

@once
    @push('scripts')
        @vite('resources/js/post-theme-preview.js')
    @endpush
@endonce
