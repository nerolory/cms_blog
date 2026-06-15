<?php

namespace App\Repositories\Contracts;

use App\Enums\AiResultStatus;
use App\Enums\AiToolCode;
use App\Models\AiToolResult;
use App\Models\Post;
use Illuminate\Database\Eloquent\Collection;

/**
 * Контракт репозитория ai tool result.
 */
interface AiToolResultRepositoryContract
{
    /**
     * Возвращает завершённые AI-результаты для поста.
     *
     * @return Collection<int, AiToolResult>
     */
    public function getCompletedForPost(Post $post): Collection;

    /**
     * upsert for post.

     *
     * @return AiToolResult
     */
    public function upsertForPost(Post $post, AiToolCode $toolCode, AiResultStatus $status, string $summary,
        ?string $detail = null): AiToolResult;
}
