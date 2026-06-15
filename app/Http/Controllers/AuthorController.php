<?php

namespace App\Http\Controllers;

use App\Services\Contracts\AuthorServiceContract;
use Illuminate\Contracts\View\View;

/**
 * HTTP-контроллер author.
 *
 * @property-read AuthorServiceContract $authorService
 */
class AuthorController extends Controller
{
    public function __construct(protected AuthorServiceContract $authorService) {}

    /**
     * show.

     *
     * @return View
     */
    public function show(int $authorId): View
    {
        $user = $this->authorService->getAuthorForProfile($authorId);
        $posts = $this->authorService->getPublishedPosts($user);

        return view('pages.authors.show', compact('user', 'posts'));
    }
}
