@extends(theme_layout('app'))

@section('title', __('tokens.page.title'))

@section('content')
    <div class="mb-4">
        <h1 class="h3">{{ __('tokens.page.title') }}</h1>
        <p class="text-muted">{{ __('tokens.page.description') }}</p>
    </div>

    <div class="card shadow-sm mb-4">
        <div class="card-body">
            <h2 class="h5">{{ __('tokens.page.balance') }}</h2>
            <p class="display-6 mb-0">{{ number_format($balance) }}</p>
        </div>
    </div>

    @if ($packages->isNotEmpty())
        <h2 class="h5 mb-3">{{ __('tokens.page.packages') }}</h2>
        <div class="row g-3">
            @foreach ($packages as $package)
                <div class="col-md-4">
                    <div class="card h-100 shadow-sm">
                        <div class="card-body d-flex flex-column">
                            <h3 class="h6">{{ $package->name }}</h3>
                            <p class="mb-1">
                                {{ __('tokens.page.package_amount', ['amount' => number_format($package->token_amount)]) }}
                            </p>
                            <p class="text-muted small mb-3">
                                {{ __('tokens.page.package_price', [
                                    'price' => number_format($package->price_cents / 100, 2),
                                    'currency' => $package->currency,
                                ]) }}
                            </p>
                            <form method="POST" action="{{ route('tokens.packages.purchase', $package) }}" class="mt-auto">
                                @csrf
                                <button type="submit" class="btn btn-primary btn-sm w-100">
                                    {{ __('tokens.page.purchase_demo') }}
                                </button>
                            </form>
                        </div>
                    </div>
                </div>
            @endforeach
        </div>
        <p class="text-muted small mt-3">{{ __('tokens.page.demo_note') }}</p>
    @else
        <p class="text-muted">{{ __('tokens.page.no_packages') }}</p>
    @endif
@endsection
