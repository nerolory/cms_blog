@props(['label', 'name', 'type' => 'text', 'value' => '', 'required' => false])

<div class="mb-3">
    <label for="{{ $name }}" class="form-label">{{ $label }}</label>
    <input type="{{ $type }}" name="{{ $name }}" id="{{ $name }}"
        @unless ($type === 'password')
            value="{{ old($name, $value) }}"
        @endunless
        @required($required) {{ $attributes->merge(['class' => 'form-control']) }}>
    @error($name)
        <div class="invalid-feedback d-block">{{ $message }}</div>
    @enderror
</div>
