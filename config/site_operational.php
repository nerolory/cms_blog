<?php

use App\Enums\OperationalService;
use App\Enums\ServiceCriticality;

return [
    'criticality' => [
        OperationalService::Database->value => ServiceCriticality::Critical->value,
        OperationalService::Redis->value => ServiceCriticality::Critical->value,
        OperationalService::Storage->value => ServiceCriticality::Critical->value,
        OperationalService::Cache->value => ServiceCriticality::Critical->value,
        OperationalService::QueueWorker->value => ServiceCriticality::Optional->value,
    ],

    'queue_heartbeat' => [
        'redis_key' => env('QUEUE_HEARTBEAT_REDIS_KEY', 'queue:worker:heartbeat'),
        'ttl_seconds' => (int) env('QUEUE_HEARTBEAT_TTL_SECONDS', 120),
    ],
];
