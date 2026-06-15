@props(['entries'])

@if ($entries->isNotEmpty())
    <nav class="post-toc card mb-4">
        <div class="card-body">
            <h2 class="h6 mb-2">{{ __('posts.toc.title') }}</h2>
            <ul class="list-unstyled mb-0">
                @foreach ($entries as $entry)
                    <li class="ms-{{ ($entry->level - 2) * 3 }}">
                        <a href="#{{ $entry->id }}" class="text-decoration-none">{{ $entry->text }}</a>
                    </li>
                @endforeach
            </ul>
        </div>
    </nav>
@endif
