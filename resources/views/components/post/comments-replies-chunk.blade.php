@props(['post', 'threadId', 'replies', 'reactionSummaries'])



@foreach ($replies as $reply)

    <x-post.comment-item :post="$post" :comment="$reply"

        :reaction-summary="$reactionSummaries->get($reply->id)"

        :indented="true" />

@endforeach

