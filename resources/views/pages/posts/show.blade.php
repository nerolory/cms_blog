@extends(theme_layout('app'))

@section('title', $seo->title)

@push('meta')
    <x-seo.meta :seo="$seo" />
@endpush

@push('head-jsonld')
    <x-seo.json-ld-article :seo="$seo" />
@endpush

@section('content')
    @if ($isPreview ?? false)
        @php($presenter = $previewPresenter)
        <x-post.preview-banner :back-url="$presenter->backUrl()" />
    @else
        @inject('postPresenterFactory', App\Presenters\PostPresenterFactory::class)
        @php($presenter = $postPresenterFactory->for($post))
    @endif

    <div class="mb-4">
        <a href="{{ route('posts.index') }}"
            class="small text-muted text-decoration-none">{{ __('posts.web.back_to_list') }}</a>
    </div>

    <article @class([
        'post-article card shadow-sm',
        'post-article--themed' => $presenter->hasCustomTheme(),
        'post-article--has-bg' => $presenter->hasBackgroundImage(),
    ])
        @if ($presenter->hasCustomTheme()) style="{{ $presenter->themeStyleString() }}" @endif>
        @if ($presenter->hasFeaturedImage())
            <img src="{{ $presenter->featuredImageUrl() }}" alt="{{ $post->title }}"
                class="post-article__featured img-fluid w-100">
        @endif

        <div class="post-article__panel card-body">
            <div class="d-flex flex-wrap align-items-start justify-content-between gap-3 mb-4">
                <h1 class="h2 mb-0 post-article__title">{{ $post->title }}</h1>
                @unless ($isPreview ?? false)
                    <x-post.actions :post="$post" :can-update-post="$canUpdatePost" :can-open-admin="$canOpenAdmin" />
                @endunless
            </div>

            <x-post.meta :post="$post" />

            @if ($post->excerpt)
                <p class="lead text-muted mb-4">{{ $post->excerpt }}</p>
            @endif

            <x-post.toc :entries="$toc" />

            <div class="post-body text-break">{!! $bodyWithToc !!}</div>
        </div>
    </article>

    @unless ($isPreview ?? false)
        <x-post.ai-insights :insights="$aiInsights" :post="$post" />
        <x-post.engagement :engagement="$engagement" :post="$post" :seo="$seo" :reading-minutes="$readingMinutes" :engagement-version="$engagementVersion" />
    @endunless
@endsection

@pushOnce('scripts')
    @vite('resources/js/post-body-code.js')
@endPushOnce
