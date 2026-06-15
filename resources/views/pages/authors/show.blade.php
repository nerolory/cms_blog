@extends(theme_layout('app'))

@section('title', $user->name)

@section('content')
    <div class="mb-4">
        <h1 class="h2">{{ $user->name }}</h1>
        <p class="text-muted">{{ __('authors.posts_count', ['count' => $posts->total()]) }}</p>
    </div>

    <div class="vstack gap-3">
        @forelse ($posts as $post)
            <article class="card shadow-sm">
                <div class="card-body">
                    <h2 class="h5 mb-1">
                        <a href="{{ route('posts.show', $post) }}" class="text-decoration-none">{{ $post->title }}</a>
                    </h2>
                    <p class="text-muted small mb-2">{{ $post->published_at?->format('d.m.Y') }}</p>
                    <p class="mb-0">{{ $post->excerpt }}</p>
                </div>
            </article>
        @empty
            <p class="text-muted">{{ __('authors.empty') }}</p>
        @endforelse
    </div>

    <div class="mt-4">{{ $posts->links() }}</div>
@endsection
