@props(['reactionCounts'])

<div {{ $attributes->merge(['class' => 'd-flex flex-wrap gap-2']) }}>
    @foreach (\App\Enums\ReactionType::all() as $type)
        @php($count = $reactionCounts->get($type->value, 0))
        @if ($count > 0)
            <span class="badge border">{{ $type->emoji() }} {{ $count }}</span>
        @endif
    @endforeach
</div>
