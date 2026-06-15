@props(['published' => false])

<span>{{ $published ? __('posts.legacy.published') : __('posts.legacy.draft') }}</span>
