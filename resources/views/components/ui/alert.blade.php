@props([
    'type' => 'success',
    'message' => '',
])

<div {{ $attributes->merge(['class' => 'alert alert-' . $type, 'role' => 'alert']) }}>
    {{ $message }}
</div>
