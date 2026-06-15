@props([
    'post' => null,
    'showAuthorField' => false,
    'showSlugField' => false,
    'showVisibilityField' => false,
    'authorOptions' => [],
    'visibilityOptions' => [],
])

@inject('postPresenterFactory', App\Presenters\PostPresenterFactory::class)
@php($presenter = $post !== null ? $postPresenterFactory->for($post) : null)

<div class="vstack gap-3">
    <x-form.text-input :label="__('posts.fields.title')" name="title" :value="$post->title ?? ''" minlength="3" maxlength="255" required />

    @if ($showSlugField)
        <x-form.slug-input :label="__('posts.fields.slug_optional')" name="slug" :value="$post->slug ?? ''" />
    @endif

    <x-form.textarea :label="__('posts.fields.excerpt')" name="excerpt" :value="$post->excerpt ?? ''" rows="2" />

    @if ($showVisibilityField)
        <x-form.select :label="__('posts.fields.visibility')" name="visibility" :options="$visibilityOptions" :selected="$post->visibility ?? 'guest'" />
    @endif

    <x-form.image-field :label="__('posts.fields.featured_image')" name="featured_image" :current-url="$presenter?->hasFeaturedImage() ? $presenter->featuredImageUrl() : null" remove-name="remove_featured_image" />

    <x-form.image-field :label="__('posts.fields.background_image')" name="background_image" :current-url="$presenter?->hasBackgroundImage() ? $presenter->backgroundImageUrl() : null"
        remove-name="remove_background_image" />

    <x-form.theme-fields :post="$post" />

    <x-form.editor-mode :selected="old('editor_mode', $post?->editor_mode?->value ?? 'simple')" />

    <x-form.rich-textarea :label="__('posts.fields.body')" name="body" :value="$post->body ?? ''" :rows="8" :required="true"
        :editor-mode="old('editor_mode', $post?->editor_mode?->value ?? 'simple')" :image-upload-url="route('posts.images.store')" />

    @if ($showAuthorField)
        <x-form.author-select :label="__('posts.fields.author')" name="user_id" :selected="$post->user_id ?? ''" :options="$authorOptions" />
    @endif

    @if ($showAuthorField)
        <x-form.checkbox :label="__('posts.fields.is_published')" name="is_published" :checked="$post->is_published ?? false" />
    @endif
</div>
