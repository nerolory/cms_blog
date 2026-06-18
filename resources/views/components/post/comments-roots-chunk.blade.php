@props(['post', 'roots', 'replyCounts', 'reactionSummaries'])

@foreach ($roots as $comment)
    <x-post.comment-thread :post="$post" :comment="$comment"
        :reaction-summary="$reactionSummaries->get($comment->id)"
        :reply-counts="$replyCounts" />
@endforeach
