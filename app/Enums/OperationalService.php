<?php

namespace App\Enums;

/**
 * Перечисление operational.
 */
enum OperationalService: string
{
    case Database = 'database';
    case Redis = 'redis';
    case Storage = 'storage';
    case Cache = 'cache';
    case QueueWorker = 'queue_worker';
}
