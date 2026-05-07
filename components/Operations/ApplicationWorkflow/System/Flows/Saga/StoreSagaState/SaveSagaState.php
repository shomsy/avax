<?php

declare(strict_types=1);

namespace Avax\Components\Operations\ApplicationWorkflow\System\Flows\Saga\StoreSagaState;

/**
 * SaveSagaState - writes the latest saga state with optional optimistic concurrency checking.
 */
final readonly class SaveSagaState
{
    /**
     * @param array<string, SagaState> $states
     */
    public function save(array &$states, SagaState $sagaState, ?int $expectedVersion = null) : SagaState
    {
        $current = $states[$sagaState->instanceId] ?? null;

        if ($expectedVersion !== null && $current !== null && $current->version !== $expectedVersion) {
            throw SagaStateStoreFailure::concurrentUpdate(instanceId: $sagaState->instanceId);
        }

        $states[$sagaState->instanceId] = $sagaState;

        return $sagaState;
    }
}
