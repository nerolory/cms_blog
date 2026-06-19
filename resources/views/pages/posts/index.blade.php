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
                <article @class([
                    'card shadow-sm post-card',
                    'post-card--themed' => $presenter->hasCustomTheme(),
                    'post-card--has-bg' => $presenter->hasBackgroundImage(),
                ])
                    @if ($presenter->hasCustomTheme()) style="{{ $presenter->themeStyleString() }}" @endif>
                    @if ($presenter->hasFeaturedImage())
                        <img src="{{ $presenter->featuredImageUrl() }}" alt="{{ $post->title }}"
                            class="card-img-top post-card__image">
                    @endif
                    <div class="card-body post-card__panel post-card__body">
                        <h2 class="h5 card-title mb-2">
                            <a href="{{ route('posts.show', $post) }}" class="post-card__title">
                                {{ $post->title }}
                            </a>
                        </h2>

                        @if ($post->excerpt)
                            <p class="post-card__muted mb-3">{{ Str::limit($post->excerpt, 150) }}</p>
                        @endif

                        <div class="d-flex flex-wrap gap-3 small post-card__muted">
                            @if ($post->user)
                                <span>{{ __('posts.web.author', ['name' => $post->user->name]) }}</span>
                            @endif
                            <span>{{ $post->published_at->format('d.m.Y') }}</span>
                        </div>

                        @php($engagement = ($listingEngagement ?? collect())->get((int) $post->id))
                        <x-post.list-engagement :engagement="$engagement ??
                            new \App\DTO\PostListEngagementItem(viewsCount: 0, reactionCounts: collect())" class="mt-2" />
                    </div>
                </article>
            @endforeach
        </div>

        <div class="mt-4">
            {{ $posts->links() }}
        </div>
    @endif
@endsection
