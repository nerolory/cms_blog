<?php

namespace App\Services\Contracts;

/**
 * Контракт сервиса feed.
 */
interface FeedServiceContract
{
    /**
     * build rss.

     *
     * @return string
     */
    public function buildRss(): string;

    /**
     * build atom.

     *
     * @return string
     */
    public function buildAtom(): string;
}
