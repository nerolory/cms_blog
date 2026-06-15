<?php

namespace App\Http\Controllers;

use App\DTO\SearchFilters;
use App\Models\User;
use App\Services\Contracts\SearchPageServiceContract;
use App\Services\Contracts\SearchServiceContract;
use App\Support\TypeCast;
use Illuminate\Contracts\View\View;
use Illuminate\Http\Request;
use Illuminate\Support\Collection;

/**
 * HTTP-контроллер search.
 *
 * @property-read SearchServiceContract $searchService
 * @property-read SearchPageServiceContract $searchPage
 */
class SearchController extends Controller
{
    public function __construct(protected SearchServiceContract $searchService,
        protected SearchPageServiceContract $searchPage) {}

    /**
     * index.

     *
     * @return View
     */
    public function index(Request $request): View
    {
        /** @var User|null $user */
        $user = auth()->user();
        $filters = SearchFilters::fromRequest(query: (string) $request->query('q', ''),
            categoryId: $request->integer('category') ?: null, tagIds: $this->tagIdsFromRequest($request),
            authorId: $request->integer('author') ?: null, dateFrom: $request->string('from')->toString() ?: null,
            dateTo: $request->string('to')->toString() ?: null, page: $request->integer('page', 1));
        $results = $this->searchService->search($filters, $user);
        $options = $this->searchPage->filterOptions();

        return view('pages.search.index', ['results' => $results, 'filters' => $filters,
            'categories' => $options->categories, 'tags' => $options->tags, 'query' => $filters->query]);
    }

    /**
     * @return Collection<int, int>
     */
    private function tagIdsFromRequest(Request $request): Collection
    {
        $tags = $request->input('tags', []);
        if (! is_array($tags)) {
            return collect();
        }

        /** @var Collection<int, int> */
        return collect($tags)->map(fn (mixed $id): int => TypeCast::int($id))->filter(fn (int $id): bool => $id > 0)
            ->values();
    }
}
