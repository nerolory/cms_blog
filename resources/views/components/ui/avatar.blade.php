@props(['user', 'size' => 'md'])

@inject('userPresenterFactory', App\Presenters\UserPresenterFactory::class)

@php
    $presenter = $userPresenterFactory->for($user);
    $sizeClass = match ($size) {
        'sm' => 'avatar-sm',
        default => 'avatar-md',
    };
    $pixel = $size === 'sm' ? 32 : 48;
    $initial = mb_strtoupper(mb_substr($user->name, 0, 1));
    $url = $presenter->avatarUrl();
@endphp

<span class="avatar-wrapper d-inline-flex position-relative">
    @if ($url)
        <img src="{{ $url }}" alt="{{ $user->name }}" class="rounded-circle {{ $sizeClass }} avatar-image"
            width="{{ $pixel }}" height="{{ $pixel }}" loading="lazy"
            onerror="this.classList.add('d-none'); this.nextElementSibling?.classList.remove('d-none');">
    @endif
    <span @class([
        'rounded-circle bg-secondary text-white d-inline-flex align-items-center justify-content-center avatar-fallback',
        $sizeClass,
        'd-none' => $url !== null,
    ])>
        {{ $initial }}
    </span>
</span>
