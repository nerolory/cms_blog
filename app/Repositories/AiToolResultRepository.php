<?php

namespace App\Repositories;

use App\Enums\AiResultStatus;
use App\Enums\AiToolCode;
use App\Models\AiToolResult;
use App\Models\Post;
use App\Repositories\Contracts\AiToolResultRepositoryContract;
use Illuminate\Database\Eloquent\Collection;

/**
 * Репозиторий ai tool result.
 *
 * @property-read AiToolResult $result
 */
class AiToolResultRepository implements AiToolResultRepositoryContract
{
    public function __construct(protected AiToolResult $result) {}

    /**
     * Возвращает завершённые AI-результаты для поста.
     *
     * @return Collection<int, AiToolResult>
     */
    public function getCompletedForPost(Post $post): Collection
    {
        return $this->result->newQuery()->where('subject_type', Post::class)->where('subject_id',
            $post->id)->where('status', AiResultStatus::Completed)->orderByDesc('completed_at')->get();
    }

    /**
     * upsert for post.

     *
     * @return AiToolResult
     */
    public function upsertForPost(Post $post, AiToolCode $toolCode, AiResultStatus $status, string $summary,
        ?string $detail = null): AiToolResult
    {
        $payload = ['summary' => $summary];
        if ($detail !== null) {
            $payload['detail'] = $detail;
        }

        return $this->result->newQuery()->updateOrCreate(['subject_type' => Post::class, 'subject_id' => $post->id,
            'tool_code' => $toolCode], ['status' => $status, 'payload' => $payload,
                'metadata' => ['source' => 'demo_execution'],
                'completed_at' => $status === AiResultStatus::Completed ? now() : null]);
    }
}
