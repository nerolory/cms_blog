@extends(theme_layout('app'))

@section('title', __('search.title'))

@section('content')
    <div class="mb-4">
        <h1 class="h2">{{ __('search.title') }}</h1>
    </div>

    <form method="GET" action="{{ route('search.index') }}" class="card card-body shadow-sm mb-4">
        <div class="row g-3">
            <div class="col-md-6">
                <label class="form-label" for="q">{{ __('search.query') }}</label>
                <input type="search" name="q" id="q" value="{{ $query }}" class="form-control">
            </div>
            <div class="col-md-3">
                <label class="form-label" for="category">{{ __('search.category') }}</label>
                <select name="category" id="category" class="form-select">
                    <option value="">{{ __('search.all_categories') }}</option>
                    @foreach ($categories as $category)
                        <option value="{{ $category->id }}" @selected($filters->categoryId === $category->id)>{{ $category->name }}</option>
                    @endforeach
                </select>
            </div>
            <div class="col-md-3">
                <label class="form-label" for="from">{{ __('search.date_from') }}</label>
                <input type="date" name="from" id="from" value="{{ $filters->dateFrom }}" class="form-control">
            </div>
            <div class="col-md-3">
                <label class="form-label" for="to">{{ __('search.date_to') }}</label>
                <input type="date" name="to" id="to" value="{{ $filters->dateTo }}" class="form-control">
            </div>
            <div class="col-12">
                <button type="submit" class="btn btn-primary">{{ __('search.submit') }}</button>
            </div>
        </div>
    </form>

    @if ($results->isEmpty())
        <div class="alert alert-light border">{{ __('search.empty') }}</div>
    @else
        <div class="vstack gap-3">
            @foreach ($results as $post)
                <article class="card shadow-sm">
                    <div class="card-body">
                        <h2 class="h5 mb-1">
                            <a href="{{ route('posts.show', $post) }}"
                                class="text-decoration-none">{{ $post->title }}</a>
                        </h2>
                        @if ($post->category)
                            <span class="badge bg-secondary mb-2">{{ $post->category->name }}</span>
                        @endif
                        <p class="text-muted mb-0">{{ $post->excerpt }}</p>
                    </div>
                </article>
            @endforeach
        </div>

        <div class="mt-4">{{ $results->withQueryString()->links() }}</div>
    @endif
@endsection
