@props(['post', 'comment', 'summary'])

@auth
    <form method="POST" action="{{ route('posts.comments.reactions.store', [$post, $comment]) }}"
        class="d-inline-flex flex-wrap gap-1 align-items-center" data-engagement-reaction-form>
        @csrf
        <input type="hidden" name="type" value="" data-engagement-reaction-type>
        @foreach (\App\Enums\ReactionType::all() as $type)
            @php($count = $summary->counts->get($type->value, 0))
            @if ($count > 0 || $summary->userReaction === $type->value)
                <button type="button" data-reaction-type="{{ $type->value }}"
                    class="btn btn-sm py-0 px-1 {{ $summary->userReaction === $type->value ? 'btn-primary' : 'btn-outline-secondary' }}">
                    {{ $type->emoji() }} {{ $count }}
                </button>
            @endif
        @endforeach
    </form>
@else
    <x-post.reaction-badges :reaction-counts="$summary->counts" class="gap-1" />
@endauth
