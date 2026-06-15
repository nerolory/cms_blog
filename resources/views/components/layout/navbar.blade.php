<nav class="navbar navbar-expand-lg border-bottom bg-body mb-3">
    <div class="container">
        <a class="navbar-brand" href="{{ route('home') }}">{{ config('app.name') }}</a>

        <button class="navbar-toggler" type="button" data-bs-toggle="collapse" data-bs-target="#mainNav"
            aria-controls="mainNav" aria-expanded="false" aria-label="Menu">
            <span class="navbar-toggler-icon"></span>
        </button>

        <div class="collapse navbar-collapse" id="mainNav">
            <ul class="navbar-nav me-auto">
                <li class="nav-item">
                    <a class="nav-link" href="{{ route('posts.index') }}">{{ __('layout.nav.posts') }}</a>
                </li>
            </ul>

            <ul class="navbar-nav ms-auto align-items-lg-center gap-lg-2">
                <li class="nav-item">
                    <form action="{{ route('locale.update') }}" method="POST" class="d-flex gap-1">
                        @csrf
                        <select name="locale" class="form-select form-select-sm" onchange="this.form.submit()">
                            <option value="ru" @selected(app()->getLocale() === 'ru')>{{ __('layout.locale.ru') }}</option>
                            <option value="en" @selected(app()->getLocale() === 'en')>{{ __('layout.locale.en') }}</option>
                        </select>
                    </form>
                </li>
                @auth
                    <li class="nav-item">
                        <a class="nav-link" href="{{ route('tokens.show') }}">{{ __('layout.nav.tokens') }}</a>
                    </li>
                    <li class="nav-item d-flex align-items-center gap-2">
                        <x-ui.avatar :user="auth()->user()" size="sm" />
                        <a class="nav-link" href="{{ route('profile.edit') }}">{{ __('layout.nav.profile') }}</a>
                    </li>
                    <li class="nav-item">
                        <form action="{{ route('logout') }}" method="POST">
                            @csrf
                            <button type="submit" class="btn btn-link nav-link">{{ __('layout.nav.logout') }}</button>
                        </form>
                    </li>
                @else
                    <li class="nav-item">
                        <a class="nav-link" href="{{ route('login') }}">{{ __('layout.nav.login') }}</a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link" href="{{ route('register') }}">{{ __('layout.nav.register') }}</a>
                    </li>
                @endauth
            </ul>
        </div>
    </div>
</nav>
