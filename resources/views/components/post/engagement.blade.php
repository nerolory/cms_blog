@props(['engagement', 'post', 'seo', 'readingMinutes' => 1])

<div class="post-engagement mb-4">
    <div class="d-flex flex-wrap align-items-center gap-3 text-muted small mb-3">
        <span>{{ __('engagement.views', ['count' => number_format($engagement->viewsCount)]) }}</span>
        <span>{{ __('engagement.reading_time', ['minutes' => $readingMinutes ?? 1]) }}</span>
    </div>

    @auth
        <form method="POST" action="{{ route('posts.reactions.store', $post) }}" class="d-flex flex-wrap gap-2 mb-4">
            @csrf
            @foreach (\App\Enums\ReactionType::all() as $type)
                @php($count = $engagement->reactionCounts->get($type->value, 0))
                <button type="submit" name="type" value="{{ $type->value }}"
                    class="btn btn-sm {{ $engagement->userReaction === $type->value ? 'btn-primary' : 'btn-outline-secondary' }}">
                    {{ $type->emoji() }} {{ $count }}
                </button>
            @endforeach
        </form>
    @else
        <div class="d-flex flex-wrap gap-2 mb-4">
            @foreach (\App\Enums\ReactionType::all() as $type)
                @php($count = $engagement->reactionCounts->get($type->value, 0))
                @if ($count > 0)
                    <span class="badge bg-light text-dark border">{{ $type->emoji() }} {{ $count }}</span>
                @endif
            @endforeach
        </div>
    @endauth

    <x-post.share-buttons :seo="$seo" />

    <section class="mt-4">
        <h2 class="h5">{{ __('engagement.comments.title') }}</h2>

        @auth
            <form method="POST" action="{{ route('posts.comments.store', $post) }}" class="mb-4">
                @csrf
                <div class="mb-2">
                    <textarea name="body" class="form-control" rows="3" required maxlength="2000"
                        placeholder="{{ __('engagement.comments.placeholder') }}"></textarea>
                </div>
                <button type="submit" class="btn btn-primary btn-sm">{{ __('engagement.comments.submit') }}</button>
            </form>
        @else
            <p class="text-muted small">{{ __('engagement.comments.login_required') }}</p>
        @endauth

        @forelse ($engagement->rootComments as $comment)
            <div class="border rounded p-3 mb-3">
                <div class="d-flex justify-content-between align-items-start gap-2">
                    <strong>{{ $comment->user?->name }}</strong>
                    <small class="text-muted">{{ $comment->created_at?->diffForHumans() }}</small>
                </div>
                <p class="mb-2 mt-1">{{ $comment->body }}</p>

                @auth
                    <form method="POST" action="{{ route('posts.comments.store', $post) }}" class="mb-2">
                        @csrf
                        <input type="hidden" name="parent_id" value="{{ $comment->id }}">
                        <textarea name="body" class="form-control form-control-sm mb-1" rows="2" required maxlength="2000"
                            placeholder="{{ __('engagement.comments.reply_placeholder') }}"></textarea>
                        <button type="submit"
                            class="btn btn-outline-primary btn-sm">{{ __('engagement.comments.reply') }}</button>
                    </form>
                @endauth

                @foreach ($comment->replies as $reply)
                    <div class="ms-4 border-start ps-3 mt-2">
                        <div class="d-flex justify-content-between">
                            <strong class="small">{{ $reply->user?->name }}</strong>
                            <small class="text-muted">{{ $reply->created_at?->diffForHumans() }}</small>
                        </div>
                        <p class="small mb-0">{{ $reply->body }}</p>
                    </div>
                @endforeach
            </div>
        @empty
            <p class="text-muted">{{ __('engagement.comments.empty') }}</p>
        @endforelse
    </section>
</div>
