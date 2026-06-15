@extends(theme_layout('app'))

@section('title', __('auth.login.title'))

@section('content')
    <div class="row justify-content-center">
        <div class="col-md-6 col-lg-5">
            <div class="card shadow-sm">
                <div class="card-body">
                    <h1 class="h4 mb-4">{{ __('auth.login.heading') }}</h1>

                    <form method="POST" action="{{ route('login') }}">
                        @csrf

                        <x-form.text-input :label="__('auth.login.email')" name="email" type="email" :value="old('email')" required
                            autocomplete="email" />

                        <x-form.text-input :label="__('auth.login.password')" name="password" type="password" required
                            autocomplete="current-password" />

                        <x-form.checkbox :label="__('auth.login.remember')" name="remember" :checked="old('remember')" />

                        <x-ui.button type="submit" variant="primary"
                            class="w-100">{{ __('auth.login.submit') }}</x-ui.button>
                    </form>

                    <p class="small text-muted mt-3 mb-0">
                        <a href="{{ route('password.request') }}">{{ __('auth.password.forgot_heading') }}</a>
                    </p>

                    <p class="small text-muted mt-2 mb-0">
                        {{ __('auth.login.no_account') }}
                        <a href="{{ route('register') }}">{{ __('auth.login.register_link') }}</a>
                    </p>
                </div>
            </div>
        </div>
    </div>
@endsection
