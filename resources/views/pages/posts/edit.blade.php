@extends(theme_layout('app'))

@section('title', __('posts.web.edit_title'))

@section('content')
    <div class="mb-4">
        <a href="{{ route('posts.show', $post) }}"
            class="small text-muted text-decoration-none">{{ __('posts.web.back_to_post') }}</a>
        <h1 class="h2 mt-2">{{ __('posts.web.edit_heading') }}</h1>
    </div>

    @if ($errors->has('form_error'))
        <x-ui.alert type="danger" :message="$errors->first('form_error')" class="mb-4" />
    @endif

    <form id="post-edit-form" action="{{ route('posts.update', $post) }}" method="POST" enctype="multipart/form-data"
        class="card shadow-sm" data-save-disabled-hint="{{ __('posts.messages.update_disabled_hint') }}">
        <div class="card-body">
            @csrf
            @method('PUT')

            <x-post.form :post="$post" :show-slug-field="true" :show-author-field="$canManageAuthors" :author-options="$authorOptions" :show-visibility-field="$showVisibilityField"
                :visibility-options="$visibilityOptions" />

            <div class="mt-4 d-flex gap-2 flex-wrap">
                <span id="post-update-wrapper" class="d-inline-block" tabindex="0" data-bs-toggle="tooltip"
                    data-bs-title="{{ __('posts.messages.update_disabled_hint') }}">
                    <x-ui.button type="submit" variant="dark" id="post-update-btn" disabled>
                        {{ __('posts.web.update') }}
                    </x-ui.button>
                </span>
                <x-ui.button type="submit" variant="outline-secondary"
                    formaction="{{ route('posts.preview.store.existing', $post) }}" formtarget="_blank" formnovalidate>
                    {{ __('posts.preview.action') }}
                </x-ui.button>
                <a href="{{ route('posts.show', $post) }}"
                    class="btn btn-outline-secondary btn-sm">{{ __('posts.web.cancel') }}</a>
            </div>
        </div>
    </form>

    @if ($versions->isNotEmpty())
        <div class="card shadow-sm mt-4">
            <div class="card-body">
                <h2 class="h5">{{ __('posts.versions.title') }}</h2>
                <p class="text-muted small mb-3">{{ __('posts.versions.hint') }}</p>
                <ul class="list-group list-group-flush">
                    @foreach ($versions as $version)
                        <li class="list-group-item px-0 d-flex justify-content-between align-items-start gap-3">
                            <div>
                                <span class="fw-semibold">#{{ $version->version_number }}</span>
                                <span class="text-muted small ms-2">{{ $version->created_at?->format('d.m.Y H:i') }}</span>
                                <div class="small text-muted mt-1">
                                    {{ \Illuminate\Support\Str::limit($version->snapshot['title'] ?? '', 60) }}</div>
                            </div>
                        </li>
                    @endforeach
                </ul>
            </div>
        </div>
    @endif
@endsection

@push('scripts')
    @vite('resources/js/post-form.js')
@endpush
