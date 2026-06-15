<?php

namespace App\Http\Controllers;

use App\Services\Contracts\FeedServiceContract;
use Illuminate\Http\Response;

/**
 * HTTP-контроллер feed.

 *
 * @property-read FeedServiceContract $feedService
 */
class FeedController extends Controller
{
    public function __construct(protected FeedServiceContract $feedService) {}

    /**
     * rss.

     *
     * @return Response
     */
    public function rss(): Response
    {
        return response($this->feedService->buildRss(), 200, ['Content-Type' => 'application/rss+xml; charset=UTF-8']);
    }

    /**
     * atom.

     *
     * @return Response
     */
    public function atom(): Response
    {
        return response($this->feedService->buildAtom(), 200,
            ['Content-Type' => 'application/atom+xml; charset=UTF-8']);
    }
}
