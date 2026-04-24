<?php

declare(strict_types=1);

namespace Avax\ApplicationWorkflow\Saga\StoreSagaState;

/**
 * SaveSagaState - writes the latest saga state with optional optimistic concurrency checking.
 */
final readonly class SaveSagaState
{
    /**
     * @param array<string, SagaState> $states
     */
    public function save(array &$states, SagaState $state, int|null $expectedVersion = null) : SagaState
    {
        $current = $states[$state->instanceId] ?? null;

        if ($expectedVersion !== null && $current !== null && $current->version !== $expectedVersion) {
            throw SagaStateStoreFailure::concurrentUpdate(instanceId: $state->instanceId);
        }

        $states[$state->instanceId] = $state;

        return $state;
    }
}
