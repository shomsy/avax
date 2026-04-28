<?php

declare(strict_types=1);

namespace Avax\Components\Operations\ApplicationWorkflow\System\Flows\Saga\RunSagaStep;

use Avax\Components\Operations\ApplicationWorkflow\System\Flows\Saga\StoreSagaState\SagaState;
use Avax\Components\Operations\ApplicationWorkflow\System\Flows\Saga\StoreSagaState\StoreSagaState;

/**
 * LoadSagaInstance - reads the stored saga state before one step can run.
 */
final readonly class LoadSagaInstance
{
    public function load(StoreSagaState $storeSagaState, string $instanceId) : SagaState
    {
        return $storeSagaState->read(instanceId: $instanceId);
    }
}
