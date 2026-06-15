<?php

namespace App\Services;

use App\DTO\AiJobDispatchData;
use App\DTO\AiJobDispatchResult;
use App\Jobs\DispatchAiGatewayJob;
use App\Services\Contracts\AiGatewayServiceContract;
use Illuminate\Support\Str;

/**
 * Фасад AI gateway: постановка HTTP-dispatch в очередь без блокировки
 * запроса.
 */
class AiGatewayService implements AiGatewayServiceContract
{
    /**
     * dispatch.

     *
     * @return AiJobDispatchResult
     */
    public function dispatch(AiJobDispatchData $job): AiJobDispatchResult
    {
        $localId = Str::uuid()->toString();
        DispatchAiGatewayJob::dispatch($job);

        return new AiJobDispatchResult(jobId: $localId, status: 'queued');
    }
}
