@extends(theme_layout('app'))

@section('title', config('app.name'))

@section('content')
    <div class="row justify-content-center">
        <div class="col-lg-8">
            <div class="card shadow-sm">
                <div class="card-body p-4 p-lg-5">
                    <h1 class="h3 mb-3">{{ __('welcome.heading', ['app' => config('app.name', 'Laravel')]) }}</h1>
                    <p class="text-muted mb-4">
                        {{ __('welcome.description') }}
                    </p>

                    <ul class="list-unstyled mb-4">
                        <li class="mb-2">
                            <a href="https://laravel.com/docs" target="_blank" class="link-primary">
                                {{ __('welcome.laravel_docs') }}
                            </a>
                        </li>
                        <li>
                            <a href="https://laracasts.com" target="_blank" class="link-primary">
                                {{ __('welcome.laracasts') }}
                            </a>
                        </li>
                    </ul>

                    <a href="{{ route('posts.index') }}" class="btn btn-primary">
                        {{ __('welcome.go_to_posts') }}
                    </a>

                    <p class="text-muted small mt-4 mb-0">
                        {{ __('welcome.version', ['version' => app()->version()]) }}
                    </p>
                </div>
            </div>
        </div>
    </div>
@endsection
