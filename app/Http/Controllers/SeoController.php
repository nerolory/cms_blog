<?php

namespace App\Http\Controllers;

use App\Services\Contracts\SeoServiceContract;
use Illuminate\Http\Response;

/**
 * HTTP-контроллер seo.

 *
 * @property-read SeoServiceContract $seoService
 */
class SeoController extends Controller
{
    public function __construct(protected SeoServiceContract $seoService) {}

    /**
     * sitemap.

     *
     * @return Response
     */
    public function sitemap(): Response
    {
        return response($this->seoService->buildSitemapXml(), 200,
            ['Content-Type' => 'application/xml; charset=UTF-8']);
    }

    /**
     * robots.

     *
     * @return Response
     */
    public function robots(): Response
    {
        return response($this->seoService->buildRobotsTxt(), 200, ['Content-Type' => 'text/plain; charset=UTF-8']);
    }
}
