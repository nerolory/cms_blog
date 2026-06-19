@props(['post', 'comment', 'reactionSummary' => null, 'replyCounts'])



@php

    $summary = $reactionSummary ?? new \App\DTO\CommentReactionSummary(counts: collect());

    $replyCount = (int) $replyCounts->get($comment->id, 0);

@endphp



<div class="post-comment-thread" data-comment-thread data-thread-id="{{ $comment->id }}">

    <div class="post-comment-thread__branch border rounded">

        <div class="p-3 post-comment-thread__root">

            <x-post.comment-item :post="$post" :comment="$comment" :reaction-summary="$summary" />



            @if ($replyCount > 0)
                <button type="button" class="btn btn-link btn-sm p-0 text-decoration-none mt-1" data-comment-load-replies
                    data-url="{{ route('posts.comments.thread', [$post, $comment->id]) }}"
                    data-count="{{ $replyCount }}">

                    {{ __('engagement.comment.show_replies', ['count' => $replyCount]) }}

                </button>
            @endif

        </div>



        <div data-comment-replies data-thread-id="{{ $comment->id }}"
            class="d-none post-comment-thread__replies px-3 pb-3">
            <div data-comment-replies-sentinel class="d-none" aria-hidden="true"></div>
        </div>

    </div>

</div>
