@extends(theme_layout('app'))

@section('title', __('auth.verification.title'))

@section('content')
    <div class="row justify-content-center">
        <div class="col-md-6 col-lg-5">
            <div class="card shadow-sm">
                <div class="card-body">
                    <h1 class="h4 mb-3">{{ __('auth.verification.heading') }}</h1>
                    <p class="text-muted">{{ __('auth.verification.intro') }}</p>

                    @if (session('status'))
                        <div class="alert alert-success">{{ session('status') }}</div>
                    @endif

                    <form method="POST" action="{{ route('verification.send') }}">
                        @csrf
                        <x-ui.button type="submit" variant="primary">{{ __('auth.verification.resend') }}</x-ui.button>
                    </form>
                </div>
            </div>
        </div>
    </div>
@endsection
