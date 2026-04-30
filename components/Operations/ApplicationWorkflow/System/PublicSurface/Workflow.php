<?php

declare(strict_types=1);

namespace Avax\Components\Operations\ApplicationWorkflow\System\PublicSurface;

use Avax\Components\Operations\ApplicationWorkflow\System\Capabilities\Saga\SagaDefinition;
use Avax\Components\Operations\ApplicationWorkflow\System\Capabilities\SagaState\SagaState;
use Avax\Components\Operations\ApplicationWorkflow\System\Capabilities\SagaStore\InMemorySagaStore;
use Avax\Components\Operations\ApplicationWorkflow\System\Capabilities\SagaStore\SagaStoreInterface;
use RuntimeException;
use Throwable;

/**
 * Workflow facade for saga orchestration.
 *
 * Provides high-level operations for starting, resuming, and canceling sagas.
 */
final class Workflow
{
    private SagaStoreInterface $store;

    public function __construct(SagaStoreInterface $store = null)
    {
        $this->store = $store ?? new InMemorySagaStore();
    }

    /**
     * Run a saga definition (legacy compatibility method).
     */
    public function runSaga(SagaDefinition $definition): void
    {
        $completed = [];

        try {
            foreach ($definition->getSteps() as $step) {
                ($step->action)();
                $completed[] = $step;
            }
        } catch (Throwable $e) {
            foreach (array_reverse($completed) as $step) {
                if ($step->compensation) {
                    ($step->compensation)();
                }
            }

            throw $e;
        }
    }

    /**
     * Start a new saga with the given name and context.
     *
     * @param string $sagaName The name of the saga to start
     * @param mixed $context The initial context/data for the saga
     *
     * @return SagaResult The result of the saga execution
     */
    public function start(string $sagaName, mixed $context = []) : SagaResult
    {
        $saga = Saga::define($sagaName)->withStore($this->store);

        // Store the saga so it can be resumed later
        $this->store->save($saga);

        return $saga->execute($context);
    }

    /**
     * Resume a saga from stored state.
     *
     * @param string $sagaId The ID of the saga to resume
     *
     * @return SagaResult The result of the saga execution
     * @throws RuntimeException If the saga is not found
     */
    public function resume(string $sagaId) : SagaResult
    {
        $saga = $this->store->findById($sagaId);

        if ($saga === null) {
            throw new RuntimeException(
                sprintf('Saga with ID "%s" not found', $sagaId),
            );
        }

        // Only running or failed sagas can be resumed
        $status = $saga->getStatus();
        if ($status !== SagaState::Running && $status !== SagaState::Failed) {
            throw new RuntimeException(
                sprintf('Cannot resume saga in "%s" state', $status->value),
            );
        }

        // Re-execute from the current state
        return $saga->execute($saga->getContext());
    }

    /**
     * Cancel a saga by running compensation.
     *
     * @param string $sagaId The ID of the saga to cancel
     *
     * @return SagaResult The result of the compensation
     * @throws RuntimeException If the saga is not found
     */
    public function cancel(string $sagaId) : SagaResult
    {
        $saga = $this->store->findById($sagaId);

        if ($saga === null) {
            throw new RuntimeException(
                sprintf('Saga with ID "%s" not found', $sagaId),
            );
        }

        return $saga->compensate();
    }

    /**
     * Get the saga store.
     */
    public function store() : SagaStoreInterface
    {
        return $this->store;
    }
}
