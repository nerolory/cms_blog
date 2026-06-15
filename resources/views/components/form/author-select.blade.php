@props(['label', 'name' => 'user_id', 'selected' => '', 'options' => [], 'placeholder' => null])

@php
    $placeholderText = $placeholder ?? __('posts.fields.author_not_selected');
@endphp

<div class="mb-3">
    <label for="{{ $name }}" class="form-label">{{ $label }}</label>
    <select name="{{ $name }}" id="{{ $name }}" data-author-select="true"
        data-placeholder="{{ $placeholderText }}" {{ $attributes->merge(['class' => 'form-select']) }}>
        <option value="">{{ $placeholderText }}</option>
        @foreach ($options as $option)
            <option value="{{ $option->id }}" @selected((string) old($name, $selected) === (string) $option->id)>
                {{ $option->label }}
            </option>
        @endforeach
    </select>
    @error($name)
        <div class="invalid-feedback d-block">{{ $message }}</div>
    @enderror
</div>

@once
    @push('scripts')
        @vite('resources/js/post-author-select.js')
    @endpush
@endonce
