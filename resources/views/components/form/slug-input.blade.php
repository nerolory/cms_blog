@props(['label', 'name' => 'slug', 'value' => ''])

@php
    use App\Support\PostSlugRules;

    $maxLength = PostSlugRules::MAX_LENGTH;
    $minLength = PostSlugRules::MIN_LENGTH;
@endphp

<div class="mb-3 post-slug-field">
    <label for="{{ $name }}" class="form-label">{{ $label }}</label>
    <input type="text" name="{{ $name }}" id="{{ $name }}" value="{{ old($name, $value) }}"
        maxlength="{{ $maxLength }}" minlength="{{ $minLength }}" inputmode="url" autocomplete="off"
        spellcheck="false" data-slug-input data-forbidden-message="{{ __('posts.fields.slug_forbidden_char') }}"
        @class(['form-control', 'is-invalid' => $errors->has($name)]) {{ $attributes->except('class') }}>
    <div class="post-slug-field__forbidden-tip" data-slug-forbidden-tip hidden role="alert">
        {{ __('posts.fields.slug_forbidden_char') }}
    </div>
    <p class="small text-muted mt-2 mb-0">{{ __('posts.fields.slug_hint') }}</p>
    @error($name)
        <div class="invalid-feedback d-block">{{ $message }}</div>
    @enderror
</div>

@once
    @push('scripts')
        @vite('resources/js/post-slug-input.js')
    @endpush
@endonce
