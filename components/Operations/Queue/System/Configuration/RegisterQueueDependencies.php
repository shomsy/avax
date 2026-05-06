<?php

declare(strict_types=1);

namespace Avax\Components\Operations\Queue\System\Configuration;

use Avax\Components\Operations\Queue\System\Capabilities\Queue\SyncQueue;
use Avax\Components\Operations\Queue\System\Flows\Dispatch\DispatchJob;
use Avax\Components\Operations\Queue\System\PublicSurface\Dispatcher;

/**
 * Configuration unit to assemble Queue component services.
 */
final class RegisterQueueDependencies
{
    public function build(): Dispatcher
    {
        return new Dispatcher(
            dispatchJob: new DispatchJob(
                broker: new SyncQueue(),
            ),
        );
    }
}
