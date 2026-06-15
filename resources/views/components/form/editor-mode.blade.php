@props([
    'selected' => 'simple',
])

@php
    use App\Enums\PostEditorMode;
@endphp

<div class="mb-3">
    <span class="form-label d-block">{{ __('posts.fields.editor_mode') }}</span>
    <div class="btn-group" role="group" aria-label="{{ __('posts.fields.editor_mode') }}">
        @foreach (PostEditorMode::cases() as $mode)
            <input type="radio" class="btn-check" name="editor_mode" id="editor_mode_{{ $mode->value }}"
                value="{{ $mode->value }}" @checked(old('editor_mode', $selected) === $mode->value) data-editor-mode-input="true">
            <label class="btn btn-outline-secondary btn-sm" for="editor_mode_{{ $mode->value }}">
                {{ __('posts.editor_mode.' . $mode->value) }}
            </label>
        @endforeach
    </div>
    <div class="form-text">{{ __('posts.fields.editor_mode_hint') }}</div>
    @error('editor_mode')
        <div class="invalid-feedback d-block">{{ $message }}</div>
    @enderror
</div>
