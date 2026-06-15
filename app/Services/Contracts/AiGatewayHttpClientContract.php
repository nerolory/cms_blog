<?php

namespace App\Services\Contracts;

use App\DTO\AiJobDispatchData;
use App\DTO\AiJobDispatchResult;

/**
 * Низкоуровневый синхронный HTTP-клиент AI gateway (вызывается только
 * из queue job).
 */
interface AiGatewayHttpClientContract
{
    /**
     * send.

     *
     * @return AiJobDispatchResult
     */
    public function send(AiJobDispatchData $job): AiJobDispatchResult;
}
