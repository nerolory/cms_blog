<?php

namespace App\Listeners;

use App\Repositories\Contracts\SiteOperationalRepositoryContract;
use Illuminate\Queue\Events\Looping;

/**
 * Обработчик события record queue worker heartbeat listener.
 *
 * @property-read SiteOperationalRepositoryContract $operational
 */
class RecordQueueWorkerHeartbeatListener
{
    public function __construct(protected SiteOperationalRepositoryContract $operational) {}

    /**
     * Обрабатывает запрос или задачу.
     */
    public function handle(Looping $event): void
    {
        $this->operational->touchQueueWorkerHeartbeat();
    }
}
