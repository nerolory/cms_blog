@extends(theme_layout('app'))

@section('title', __('posts.web.create_title'))

@section('content')
    <div class="mb-4">
        <a href="{{ route('posts.index') }}"
            class="small text-muted text-decoration-none">{{ __('posts.web.back_to_list') }}</a>
        <h1 class="h2 mt-2">{{ __('posts.web.create_heading') }}</h1>
    </div>

    <form action="{{ route('posts.store') }}" method="POST" enctype="multipart/form-data" class="card shadow-sm">
        <div class="card-body">
            @csrf
            <x-post.form :show-visibility-field="true" :visibility-options="$visibilityOptions" />

            <div class="mt-4 d-flex gap-2 flex-wrap">
                <x-ui.button type="submit" variant="dark">{{ __('posts.web.save') }}</x-ui.button>
                <x-ui.button type="submit" variant="outline-secondary" formaction="{{ route('posts.preview.store') }}"
                    formtarget="_blank" formnovalidate>
                    {{ __('posts.preview.action') }}
                </x-ui.button>
            </div>
        </div>
    </form>
@endsection
