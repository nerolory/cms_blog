<?php

namespace App\Services\Contracts;

use App\DTO\AiJobDispatchData;
use App\DTO\AiJobDispatchResult;

/**
 * Контракт сервиса ai gateway.
 */
interface AiGatewayServiceContract
{
    /**
     * dispatch.

     *
     * @return AiJobDispatchResult
     */
    public function dispatch(AiJobDispatchData $job): AiJobDispatchResult;
}
