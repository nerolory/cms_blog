@extends(theme_layout('guest'))

@section('title', __('auth.password.forgot_title'))

@section('content')
    <div class="row justify-content-center">
        <div class="col-md-6 col-lg-5">
            <h1 class="h3 mb-4">{{ __('auth.password.forgot_heading') }}</h1>

            <form method="POST" action="{{ route('password.email') }}" class="vstack gap-3">
                @csrf
                <x-form.text-input :label="__('auth.password.email')" name="email" type="email" :value="old('email')" required />
                <x-ui.button type="submit">{{ __('auth.password.submit') }}</x-ui.button>
            </form>

            <p class="mt-3 mb-0">
                <a href="{{ route('login') }}">{{ __('auth.password.back_to_login') }}</a>
            </p>
        </div>
    </div>
@endsection
