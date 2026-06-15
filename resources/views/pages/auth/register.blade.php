@extends(theme_layout('app'))

@section('title', __('auth.register.title'))

@section('content')
    <div class="row justify-content-center">
        <div class="col-md-6 col-lg-5">
            <div class="card shadow-sm">
                <div class="card-body">
                    <h1 class="h4 mb-4">{{ __('auth.register.heading') }}</h1>

                    <form method="POST" action="{{ route('register') }}">
                        @csrf

                        <x-form.text-input :label="__('auth.register.name')" name="name" :value="old('name')" required autocomplete="name" />

                        <x-form.text-input :label="__('auth.register.email')" name="email" type="email" :value="old('email')" required
                            autocomplete="email" />

                        <x-form.text-input :label="__('auth.register.password')" name="password" type="password" required
                            autocomplete="new-password" />

                        <x-form.text-input :label="__('auth.register.password_confirmation')" name="password_confirmation" type="password" required
                            autocomplete="new-password" />

                        <x-ui.button type="submit" variant="primary"
                            class="w-100">{{ __('auth.register.submit') }}</x-ui.button>
                    </form>

                    <p class="small text-muted mt-3 mb-0">
                        {{ __('auth.register.has_account') }}
                        <a href="{{ route('login') }}">{{ __('auth.register.login_link') }}</a>
                    </p>
                </div>
            </div>
        </div>
    </div>
@endsection
