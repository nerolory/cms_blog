@once
    <style {!! csp_nonce_attribute() !!}>
        .fi-public-site-link {
            margin-inline-start: 10px;
            margin-top: 9px;
        }
    </style>
@endonce
<div class="fi-public-site-link flex shrink-0 items-center">
    <x-filament::link
        :href="route('home')"
        color="primary"
        :icon="\Filament\Support\Icons\Heroicon::OutlinedGlobeAlt"
        class="whitespace-nowrap text-sm font-semibold"
    >
        {{ __('layout.nav.public_site') }}
    </x-filament::link>
</div>
