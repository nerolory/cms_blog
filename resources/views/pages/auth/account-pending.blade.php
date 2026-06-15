@extends(theme_layout('app'))

@section('title', __('auth.pending.title'))

@section('content')
    <div class="row justify-content-center">
        <div class="col-md-6 col-lg-5">
            <div class="card shadow-sm">
                <div class="card-body">
                    <h1 class="h4 mb-3">{{ __('auth.pending.heading') }}</h1>
                    <p class="text-muted">{{ __('auth.pending.intro') }}</p>
                </div>
            </div>
        </div>
    </div>
@endsection
