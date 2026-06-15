<?php

namespace App\Http\Requests\Concerns;

use App\Services\Contracts\PostServiceContract;

/**
 * Внедряет PostServiceContract в Form Request через конструктор.
 *
 * @property-read PostServiceContract $postService
 */
trait InjectsPostService
{
    public function __construct(protected PostServiceContract $postService)
    {
        parent::__construct();
    }

    /**
     * Сервис постов для trait ResolvesPostFromRoute / ResolvesPostFromApiRoute.
     *
     * @return PostServiceContract
     */
    protected function postService(): PostServiceContract
    {
        return $this->postService;
    }
}
