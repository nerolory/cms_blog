@props([
    'variant' => 'primary',
    'size' => 'sm',
])

<button {{ $attributes->merge([
    'type' => 'button',
    'class' => 'btn btn-' . $variant . ' btn-' . $size,
]) }}>
    {{ $slot }}
</button>
