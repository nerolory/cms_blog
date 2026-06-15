@props(['label', 'name', 'checked' => false])

<div class="form-check mb-3">
    <input type="checkbox" name="{{ $name }}" id="{{ $name }}" value="1" @checked(old($name, $checked))
        {{ $attributes->merge(['class' => 'form-check-input']) }}>
    <label for="{{ $name }}" class="form-check-label">{{ $label }}</label>
    @error($name)
        <div class="invalid-feedback d-block">{{ $message }}</div>
    @enderror
</div>
