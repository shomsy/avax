<?php

declare(strict_types=1);

namespace Avax\Components\Operations\ApplicationWorkflow\System\Flows\Saga\StoreSagaState;

/**
 * ReadSagaState - reads a saga state or fails with the missing instance id.
 */
final readonly class ReadSagaState
{
    /**
     * @param array<string, SagaState> $states
     */
    public function read(array $states, string $instanceId): SagaState
    {
        return $states[$instanceId] ?? throw SagaStateStoreFailure::missingState(instanceId: $instanceId);
    }
}
