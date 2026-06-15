@extends(theme_layout('app'))

@section('title', __('profile.title'))

@push('styles')
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/cropperjs@1.6.2/dist/cropper.min.css">
@endpush

@section('content')
    @inject('userPresenterFactory', App\Presenters\UserPresenterFactory::class)
    @php($userPresenter = $userPresenterFactory->for($user))

    <div class="row g-4">
        <div class="col-lg-7">
            <div class="card shadow-sm">
                <div class="card-body">
                    <h1 class="h4 mb-4">{{ __('profile.heading') }}</h1>

                    <form id="profile-form" method="POST" action="{{ route('profile.update') }}"
                        data-save-disabled-hint="{{ __('profile.messages.save_disabled_hint') }}"
                        data-initial-name="{{ $user->name }}" data-initial-email="{{ $user->email }}"
                        data-initial-theme="{{ $user->theme }}">
                        @csrf
                        @method('PATCH')

                        <x-form.text-input :label="__('profile.fields.name')" name="name" :value="old('name', $user->name)" autocomplete="name"
                            required />

                        <x-form.text-input :label="__('profile.fields.email')" name="email" type="email" :value="old('email', $user->email)"
                            autocomplete="username" required />

                        <x-form.text-input :label="__('profile.fields.password')" name="password" type="password" autocomplete="new-password" />
                        <p class="small text-muted">{{ __('profile.fields.password_hint') }}</p>

                        <x-form.text-input :label="__('profile.fields.password_confirmation')" name="password_confirmation" type="password"
                            autocomplete="new-password" />

                        <x-form.select :label="__('profile.fields.theme')" name="theme" :selected="old('theme', $user->theme)" :options="[
                            'default' => __('profile.themes.default'),
                            'light' => __('profile.themes.light'),
                            'dark' => __('profile.themes.dark'),
                            'minimal' => __('profile.themes.minimal'),
                        ]" />

                        <span id="profile-save-wrapper" class="d-inline-block" tabindex="0" data-bs-toggle="tooltip"
                            data-bs-title="{{ __('profile.messages.save_disabled_hint') }}">
                            <x-ui.button type="submit" variant="primary" id="profile-save-btn" disabled>
                                {{ __('profile.actions.save') }}
                            </x-ui.button>
                        </span>
                    </form>
                </div>
            </div>
        </div>

        <div class="col-lg-5">
            <div class="card shadow-sm">
                <div class="card-body">
                    <h2 class="h5 mb-3">{{ __('profile.fields.avatar') }}</h2>

                    <div class="mb-3">
                        <x-ui.avatar :user="$user" size="md" />
                    </div>

                    @if ($userPresenter->hasAvatarRecord() && !$userPresenter->avatarAvailable())
                        <x-ui.alert type="warning" :message="__('profile.messages.avatar_not_recognized')" class="mb-3" />
                    @endif

                    <form id="avatar-upload-form" method="POST" action="{{ route('profile.avatar.store') }}"
                        enctype="multipart/form-data" class="mb-3">
                        @csrf
                        <x-form.file-input :label="__('profile.fields.avatar')" name="avatar"
                            accept="image/jpeg,image/png,image/gif,image/webp" class="visually-hidden" />
                        <p class="small text-muted">{{ __('profile.fields.avatar_hint') }}</p>
                        <x-ui.button type="button" variant="secondary" id="avatar-upload-trigger">
                            {{ __('profile.actions.upload_avatar') }}
                        </x-ui.button>
                    </form>

                    @if ($user->avatar_path)
                        <form method="POST" action="{{ route('profile.avatar.destroy') }}">
                            @csrf
                            @method('DELETE')
                            <x-ui.button type="submit"
                                variant="outline-danger">{{ __('profile.actions.delete_avatar') }}</x-ui.button>
                        </form>
                    @endif
                </div>
            </div>
        </div>
    </div>

    <div class="modal fade" id="avatar-crop-modal" tabindex="-1" aria-labelledby="avatarCropLabel" aria-hidden="true">
        <div class="modal-dialog modal-lg modal-dialog-centered">
            <div class="modal-content">
                <div class="modal-header">
                    <h2 class="modal-title h5" id="avatarCropLabel">{{ __('profile.avatar_crop.title') }}</h2>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body">
                    <div class="avatar-crop">
                        <div class="avatar-crop__stage bg-body-secondary">
                            <img id="avatar-crop-image" src="" alt="{{ __('profile.avatar_crop.title') }}"
                                class="avatar-crop__image">
                        </div>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-outline-secondary" data-bs-dismiss="modal">
                        {{ __('profile.avatar_crop.cancel') }}
                    </button>
                    <button type="button" class="btn btn-primary" id="avatar-crop-save">
                        {{ __('profile.avatar_crop.save') }}
                    </button>
                </div>
            </div>
        </div>
    </div>
@endsection

@push('scripts')
    <script src="https://cdn.jsdelivr.net/npm/cropperjs@1.6.2/dist/cropper.min.js" nonce="{{ csp_nonce() }}"></script>
    @vite(['resources/js/profile-form.js', 'resources/js/profile-avatar.js'])
@endpush
