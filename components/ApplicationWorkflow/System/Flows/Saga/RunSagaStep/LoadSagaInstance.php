<?php

declare(strict_types=1);

namespace Avax\Components\ApplicationWorkflow\System\Flows\Saga\RunSagaStep;

use Avax\Components\ApplicationWorkflow\System\Flows\Saga\StoreSagaState\SagaState;
use Avax\Components\ApplicationWorkflow\System\Flows\Saga\StoreSagaState\StoreSagaState;

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
