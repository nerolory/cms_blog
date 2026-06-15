<x-filament-panels::page>
    @if ($latestReport)
        <div class="grid gap-4 md:grid-cols-3 mb-6">
            <x-filament::section>
                <div class="text-sm text-gray-500">{{ __('admin.site_health.stats.critical') }}</div>
                <div class="text-2xl font-bold text-danger-600">{{ $latestReport->critical_count }}</div>
            </x-filament::section>
            <x-filament::section>
                <div class="text-sm text-gray-500">{{ __('admin.site_health.stats.warning') }}</div>
                <div class="text-2xl font-bold text-warning-600">{{ $latestReport->warning_count }}</div>
            </x-filament::section>
            <x-filament::section>
                <div class="text-sm text-gray-500">{{ __('admin.site_health.stats.passed') }}</div>
                <div class="text-2xl font-bold text-success-600">{{ $latestReport->passed_count }}</div>
            </x-filament::section>
        </div>

        <x-filament::section :heading="__('admin.site_health.latest_report')">
            <p class="text-sm text-gray-500 mb-4">
                {{ __('admin.site_health.completed_at', ['date' => $latestReport->completed_at?->format('d.m.Y H:i') ?? '—']) }}
                · {{ __('admin.site_health.context') }}: {{ $latestReport->context->value }}
            </p>

            <div class="divide-y">
                @foreach ($latestReport->results ?? [] as $result)
                    @php
                        $severity = $result['severity'] ?? 'passed';
                        $color = match ($severity) {
                            'critical' => 'text-danger-600',
                            'warning' => 'text-warning-600',
                            default => 'text-success-600',
                        };
                    @endphp
                    <div class="py-3">
                        <div class="font-medium {{ $color }}">{{ $result['name'] ?? '' }}</div>
                        <div class="text-sm">{{ $result['message'] ?? '' }}</div>
                        @if (!empty($result['details']))
                            <div class="text-xs text-gray-500 mt-1">{{ $result['details'] }}</div>
                        @endif
                    </div>
                @endforeach
            </div>
        </x-filament::section>
    @else
        <x-filament::section>
            <p>{{ __('admin.site_health.empty') }}</p>
        </x-filament::section>
    @endif
</x-filament-panels::page>
