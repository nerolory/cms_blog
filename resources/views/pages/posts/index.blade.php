@extends(theme_layout('app'))

@section('title', __('posts.web.title'))

@section('content')
    @inject('postPresenterFactory', App\Presenters\PostPresenterFactory::class)

    <div class="d-flex align-items-center justify-content-between mb-4">
        <h1 class="h2 mb-0">{{ __('posts.web.title') }}</h1>
        @auth
            @can('create', App\Models\Post::class)
                <a href="{{ route('posts.create') }}" class="btn btn-primary btn-sm">{{ __('posts.web.create_heading') }}</a>
            @endcan
        @endauth
    </div>

    @if ($posts->isEmpty())
        <p class="text-muted">{{ __('posts.web.empty') }}</p>
    @else
        <div class="vstack gap-3">
            @foreach ($posts as $post)
                @php($presenter = $postPresenterFactory->for($post))
                <article class="card shadow-sm post-card @if ($presenter->hasCustomTheme()) post-card--themed @endif"
                    @if ($presenter->hasCustomTheme()) style="{{ $presenter->themeStyleString() }}" @endif>
                    @if ($presenter->hasFeaturedImage())
                        <img src="{{ $presenter->featuredImageUrl() }}" alt="{{ $post->title }}"
                            class="card-img-top post-card__image">
                    @endif
                    <div class="card-body post-card__body">
                        <h2 class="h5 card-title mb-2">
                            <a href="{{ route('posts.show', $post) }}"
                                class="link-body-emphasis text-decoration-none post-card__title">
                                {{ $post->title }}
                            </a>
                        </h2>

                        @if ($post->excerpt)
                            <p class="text-muted mb-3">{{ Str::limit($post->excerpt, 150) }}</p>
                        @endif

                        <div class="d-flex flex-wrap gap-3 small text-muted">
                            @if ($post->user)
                                <span>{{ __('posts.web.author', ['name' => $post->user->name]) }}</span>
                            @endif
                            <span>{{ $post->published_at->format('d.m.Y') }}</span>
                        </div>
                    </div>
                </article>
            @endforeach
        </div>

        <div class="mt-4">
            {{ $posts->links() }}
        </div>
    @endif
@endsection
