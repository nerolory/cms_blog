<?php

namespace App\Services;

use App\DTO\AiJobDispatchData;
use App\DTO\AiJobDispatchResult;
use App\Services\Contracts\AiGatewayHttpClientContract;
use App\Support\TypeCast;
use Illuminate\Http\Client\ConnectionException;
use Illuminate\Http\Client\RequestException;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Str;
use RuntimeException;

/**
 * Синхронный HTTP-клиент внешнего AI-сервиса; не вызывать из
 * web-запроса напрямую.
 */
class AiGatewayHttpClient implements AiGatewayHttpClientContract
{
    /**
     * send.

     *
     * @return AiJobDispatchResult
     */
    public function send(AiJobDispatchData $job): AiJobDispatchResult
    {
        $baseUrl = rtrim(TypeCast::string(config('ai.service.base_url')), '/');
        $timeout = TypeCast::int(config('ai.service.timeout_seconds', 30));
        try {
            $response = Http::timeout($timeout)->acceptJson()->post("{$baseUrl}/v1/jobs",
                ['tool_code' => $job->toolCode, 'subject_type' => $job->subjectType, 'subject_id' => $job->subjectId,
                    'payload' => $job->payload, 'metadata' => $job->metadata])->throw();
        } catch (ConnectionException $exception) {
            throw new RuntimeException('AI service is unreachable.', 0, $exception);
        } catch (RequestException $exception) {
            throw new RuntimeException('AI service rejected the job dispatch.', 0, $exception);
        }
        /** @var array{job_id?: string, status?: string} $body */
        $body = $response->json();

        return new AiJobDispatchResult(jobId: (string) ($body['job_id'] ?? Str::uuid()->toString()),
            status: (string) ($body['status'] ?? 'accepted'));
    }
}
