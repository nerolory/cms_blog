<?php

namespace App\Jobs;

use App\DTO\AiJobDispatchData;
use App\Services\Contracts\AiGatewayHttpClientContract;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Queue\Queueable;

/**
 * Асинхронный HTTP-dispatch к внешнему AI-сервису (без блокировки web/worker).
 *
 * @property-read AiJobDispatchData $job
 * @property int $timeout
 */
class DispatchAiGatewayJob implements ShouldQueue
{
    use Queueable;

    public int $timeout = 120;

    public function __construct(public AiJobDispatchData $job) {}

    /**
     * Обрабатывает запрос или задачу.
     */
    public function handle(AiGatewayHttpClientContract $client): void
    {
        $client->send($this->job);
    }
}
