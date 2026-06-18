@props(['post'])

<div class="d-flex flex-wrap gap-3 small text-muted mb-4">
    @if ($post->user)
        <span>{{ __('posts.web.author', ['name' => $post->user->name]) }}</span>
    @endif
    <x-post.status-badge :published="$post->is_published" />
    <span>{{ $post->created_at->format('d.m.Y H:i') }}</span>
</div>
