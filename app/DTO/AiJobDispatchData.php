<?php

namespace App\DTO;

/**
 * DTO ai job dispatch.

 *
 * @property-read string $toolCode
 * @property-read string $subjectType
 * @property-read int $subjectId
 * @property-read array<string, mixed> $payload
 * @property-read ?array<string, mixed> $metadata
 */
readonly class AiJobDispatchData extends AbstractData
{
    /**
     * @param  array<string, mixed>  $payload
     * @param  array<string, mixed>|null  $metadata
     */
    public function __construct(public string $toolCode, public string $subjectType, public int $subjectId,
        public array $payload = [], public ?array $metadata = null) {}
}
