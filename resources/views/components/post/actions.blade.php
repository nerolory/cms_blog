@props(['post', 'canUpdatePost' => false, 'canOpenAdmin' => false])

@if ($canUpdatePost || $canOpenAdmin)
    <div {{ $attributes->merge(['class' => 'post-article__actions d-flex flex-wrap gap-2']) }}>
        @if ($canUpdatePost)
            <a href="{{ route('posts.edit', $post) }}" class="btn btn-outline-primary btn-sm" data-full-nav>
                {{ __('posts.web.edit') }}
            </a>
        @endif

        @if ($canOpenAdmin)
            <a href="{{ \App\Filament\Resources\Posts\PostResource::getUrl('edit', ['record' => $post], panel: 'admin') }}"
                class="btn btn-outline-secondary btn-sm" data-full-nav>
                {{ __('posts.web.open_in_admin') }}
            </a>
        @endif
    </div>
@endif
