@props([
    'name' => 'body',
    'label' => null,
    'value' => '',
    'rows' => 8,
    'required' => false,
    'editorMode' => 'simple',
    'imageUploadUrl' => null,
])

<div class="mb-3">
    @if ($label)
        <label for="{{ $name }}" class="form-label">{{ $label }}</label>
    @endif
    <textarea id="{{ $name }}" name="{{ $name }}" rows="{{ $rows }}"
        class="form-control @error($name) is-invalid @enderror" data-rich-text="true" data-editor-mode="{{ $editorMode }}"
        data-image-upload-url="{{ $imageUploadUrl }}" @if ($required) data-required="true" @endif>{{ old($name, $value) }}</textarea>
    @error($name)
        <div class="invalid-feedback">{{ $message }}</div>
    @enderror
</div>

@once
    @push('styles')
        <link rel="stylesheet" href="{{ asset('vendor/tinymce/skins/ui/oxide/skin.min.css') }}">
        <link rel="stylesheet" href="{{ asset('vendor/tinymce/skins/content/default/content.min.css') }}">
    @endpush
    @push('scripts')
        <script src="{{ asset('vendor/tinymce/tinymce.min.js') }}" referrerpolicy="origin" nonce="{{ csp_nonce() }}">
        </script>
        @vite('resources/js/tinymce-editor.js')
    @endpush
@endonce
