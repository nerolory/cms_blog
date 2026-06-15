@props([
    'label',
    'name',
    'currentUrl' => null,
    'removeName' => null,
    'accept' => 'image/jpeg,image/png,image/gif,image/webp',
])

<div class="mb-3 post-image-field" data-image-field="{{ $name }}">
    <label for="{{ $name }}" class="form-label">{{ $label }}</label>

    @if ($currentUrl)
        <div class="post-image-field__preview mb-2">
            <img src="{{ $currentUrl }}" alt="{{ $label }}" class="img-fluid rounded border">
        </div>
    @endif

    <input type="file" name="{{ $name }}" id="{{ $name }}" accept="{{ $accept }}"
        class="form-control @error($name) is-invalid @enderror">

    @if ($removeName && $currentUrl)
        <div class="form-check mt-2">
            <input type="checkbox" class="form-check-input" name="{{ $removeName }}" id="{{ $removeName }}"
                value="1" @checked(old($removeName))>
            <label class="form-check-label" for="{{ $removeName }}">
                {{ __('posts.fields.remove_image') }}
            </label>
        </div>
    @endif

    @error($name)
        <div class="invalid-feedback d-block">{{ $message }}</div>
    @enderror
</div>
