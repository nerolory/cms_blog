@props(['insights', 'post'])

<section class="card shadow-sm mb-4" id="ai-insights">
    <div class="card-body">
        <h2 class="h5 mb-3">{{ __('ai.insights.title') }}</h2>

        @if ($insights->hasCache())
            <div class="d-flex flex-column gap-3">
                @foreach ($insights->items as $item)
                    <article class="border rounded p-3">
                        <div class="d-flex justify-content-between align-items-start gap-2 mb-2">
                            <strong>{{ $item->label }}</strong>
                            @if ($item->completedAt)
                                <small class="text-muted">{{ $item->completedAt->diffForHumans() }}</small>
                            @endif
                        </div>
                        <p class="mb-0">{{ $item->summary }}</p>
                    </article>
                @endforeach
            </div>
        @else
            <p class="text-muted mb-3">{{ __('ai.insights.empty') }}</p>
        @endif

        @auth
            @if ($insights->hasPendingOrder)
                <div class="alert alert-info mb-0">{{ __('ai.insights.pending_order') }}</div>
            @elseif ($insights->cooldownUntil && $insights->cooldownUntil->isFuture())
                <div class="alert alert-warning mb-0">
                    {{ __('ai.insights.cooldown', ['until' => $insights->cooldownUntil->translatedFormat('d.m.Y H:i')]) }}
                </div>
            @elseif ($insights->canRequestOrder)
                <form method="POST" action="{{ route('posts.ai-analysis.store', $post) }}"
                    class="d-flex flex-wrap align-items-center gap-2">
                    @csrf
                    <button type="submit" class="btn btn-outline-primary btn-sm">
                        {{ __('ai.insights.request_button') }}
                    </button>
                    <span class="text-muted small">
                        {{ __('ai.insights.tokens_hint', [
                            'tokens' => number_format($insights->tokensRequired ?? 0),
                            'comments' => number_format($insights->commentCount),
                        ]) }}
                    </span>
                </form>
            @endif
        @endauth
    </div>
</section>
