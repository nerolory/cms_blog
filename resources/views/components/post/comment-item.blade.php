@props(['post', 'comment', 'reactionSummary' => null, 'indented' => false])

@php
    $summary = $reactionSummary ?? new \App\DTO\CommentReactionSummary(counts: collect());
@endphp

<article @class(['post-comment mb-3', 'post-comment--indented' => $indented]) id="comment-{{ $comment->id }}">
    <div class="d-flex gap-2">
        @if ($comment->user)
            <x-ui.avatar :user="$comment->user" size="sm" />
        @endif
        <div class="flex-grow-1 min-w-0">
            <div class="d-flex flex-wrap justify-content-between align-items-start gap-2">
                <strong class="small">{{ $comment->user?->name }}</strong>
                <small class="text-muted">{{ $comment->created_at?->diffForHumans() }}</small>
            </div>

            @if ($comment->shouldShowReplyReference() && $comment->replyTo?->user)
                <p class="post-comment__reply-to text-muted mb-1">
                    <a href="#comment-{{ $comment->reply_to_id }}" class="text-decoration-none">
                        {{ __('engagement.comment.reply_to', ['name' => $comment->replyTo->user->name]) }}
                    </a>
                </p>
            @endif

            <p class="mb-2 mt-1">{{ $comment->body }}</p>

            <div class="d-flex flex-wrap align-items-center post-comment__actions mb-2">
                @auth
                    <button type="button" class="btn btn-link btn-sm p-0 text-decoration-none" data-comment-reply-toggle
                        data-target="reply-form-{{ $comment->id }}">
                        {{ __('engagement.comment.reply') }}
                    </button>
                @endauth
                <x-post.comment-reactions :post="$post" :comment="$comment" :summary="$summary" />
            </div>

            @auth
                <form method="POST" action="{{ route('posts.comments.store', $post) }}" class="mb-2 d-none"
                    id="reply-form-{{ $comment->id }}" data-comment-reply-form>
                    @csrf
                    <input type="hidden" name="parent_id" value="{{ $comment->id }}">
                    <textarea name="body" class="form-control form-control-sm mb-1" rows="2" required maxlength="2000"
                        placeholder="{{ __('engagement.comment.reply_placeholder') }}"></textarea>
                    <button type="submit" class="btn btn-outline-primary btn-sm">
                        {{ __('engagement.comment.submit') }}
                    </button>
                </form>
            @endauth
        </div>
    </div>
</article>
