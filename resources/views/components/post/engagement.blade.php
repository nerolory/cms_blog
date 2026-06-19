@props(['engagement', 'post', 'seo', 'readingMinutes' => 1, 'engagementVersion' => '0', 'fragmentOnly' => false])

@php($comments = $engagement->comments)

@unless ($fragmentOnly)
    <div data-post-engagement-app data-engagement-url="{{ route('posts.engagement.show', $post) }}"
        data-stream-url="{{ route('posts.engagement.stream', $post) }}" data-engagement-version="{{ $engagementVersion }}">
    @endunless

    <div class="post-engagement mb-4">
        <div class="d-flex flex-wrap align-items-center gap-3 text-muted small mb-3">
            <span>{{ __('engagement.views', ['count' => number_format($engagement->viewsCount)]) }}</span>
            <span>{{ __('engagement.reading_time', ['minutes' => $readingMinutes ?? 1]) }}</span>
        </div>

        @auth
            <form method="POST" action="{{ route('posts.reactions.store', $post) }}" class="d-flex flex-wrap gap-2 mb-4"
                data-engagement-reaction-form>
                @csrf
                <input type="hidden" name="type" value="" data-engagement-reaction-type>
                @foreach (\App\Enums\ReactionType::all() as $type)
                    @php($count = $engagement->reactionCounts->get($type->value, 0))
                    <button type="button" data-reaction-type="{{ $type->value }}"
                        class="btn btn-sm {{ $engagement->userReaction === $type->value ? 'btn-primary' : 'btn-outline-secondary' }}">
                        {{ $type->emoji() }} {{ $count }}
                    </button>
                @endforeach
            </form>
        @else
            <div class="d-flex flex-wrap gap-2 mb-4">
                <x-post.reaction-badges :reaction-counts="$engagement->reactionCounts" />
            </div>
        @endauth

        <x-post.share-buttons :seo="$seo" />

        <section class="mt-4" data-comments-section data-post-slug="{{ $post->slug }}"
            data-roots-url="{{ route('posts.comments.index', $post) }}"
            data-has-more-roots="{{ $comments->hasMoreRoots ? '1' : '0' }}"
            data-roots-offset="{{ $comments->rootComments->count() }}">
            <h2 class="h5">{{ __('engagement.comment.title') }}</h2>

            @auth
                <form method="POST" action="{{ route('posts.comments.store', $post) }}" class="mb-4">
                    @csrf
                    <div class="mb-2">
                        <textarea name="body" class="form-control" rows="3" required maxlength="2000"
                            placeholder="{{ __('engagement.comment.placeholder') }}"></textarea>
                    </div>
                    <button type="submit" class="btn btn-primary btn-sm">{{ __('engagement.comment.submit') }}</button>
                </form>
            @else
                <p class="text-muted small">{{ __('engagement.comment.login_required') }}</p>
            @endauth

            <div data-comment-roots>
                @if ($comments->rootComments->isEmpty())
                    <p class="text-muted" data-comments-empty>{{ __('engagement.comment.empty') }}</p>
                @else
                    <x-post.comments-roots-chunk :post="$post" :roots="$comments->rootComments" :reply-counts="$comments->replyCounts"
                        :reaction-summaries="$comments->reactionSummaries" />
                @endif
                <div data-comment-roots-sentinel aria-hidden="true"></div>
            </div>
        </section>
    </div>

    <template id="comment-skeleton-template">
        <x-post.comment-skeleton />
    </template>

    @unless ($fragmentOnly)
    </div>
@endunless
