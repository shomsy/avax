<?php
$queueFile = 'components/Operations/Queue/System/Configuration/Builders/RegisterQueueDependencies.php';
$content = <<<CONTENT
<?php

declare(strict_types=1);

namespace Avax\Components\Operations\Queue\System\Configuration\Builders;

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
        \$jobRegistry = new \Avax\Components\Operations\Queue\System\Capabilities\Job\JobRegistry();
        \$broker = new \Avax\Components\Operations\Queue\System\Capabilities\Queue\SyncQueue();

        return new Dispatcher(
            dispatchJob: new DispatchJob(jobRegistry: \$jobRegistry),
            queueBroker: \$broker,
        );
    }
}
CONTENT;
file_put_contents($queueFile, $content);
