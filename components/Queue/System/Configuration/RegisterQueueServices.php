<?php

declare(strict_types=1);

namespace Avax\Components\Queue\System\Configuration;

use Avax\Components\Queue\System\Capabilities\Queue\SyncQueue;
use Avax\Components\Queue\System\Flows\Dispatch\DispatchJob;
use Avax\Components\Queue\System\PublicSurface\Dispatcher;

/**
 * Configuration unit to assemble Queue component services.
 */
final class RegisterQueueServices
{
    public function build() : Dispatcher
    {
        return new Dispatcher(
            dispatchJob: new DispatchJob(
                             broker: new SyncQueue()
                         )
        );
    }
}