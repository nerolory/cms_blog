@extends(theme_layout('guest'))

@section('title', __('auth.password.reset_title'))

@section('content')
    <div class="row justify-content-center">
        <div class="col-md-6 col-lg-5">
            <h1 class="h3 mb-4">{{ __('auth.password.reset_heading') }}</h1>

            <form method="POST" action="{{ route('password.update') }}" class="vstack gap-3">
                @csrf
                <input type="hidden" name="token" value="{{ $token }}">
                <x-form.text-input :label="__('auth.password.email')" name="email" type="email" :value="old('email', $email)" required />
                <x-form.text-input :label="__('auth.password.new_password')" name="password" type="password" required />
                <x-form.text-input :label="__('auth.password.confirm_password')" name="password_confirmation" type="password" required />
                <x-ui.button type="submit">{{ __('auth.password.submit') }}</x-ui.button>
            </form>
        </div>
    </div>
@endsection
