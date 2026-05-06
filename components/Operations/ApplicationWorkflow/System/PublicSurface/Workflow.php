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
final readonly class Workflow
{
    private SagaStoreInterface $sagaStore;

    public function __construct(?SagaStoreInterface $sagaStore = null)
    {
        $this->sagaStore = $sagaStore ?? new InMemorySagaStore();
    }

    /**
     * Run a saga definition (legacy compatibility method).
     */
    public function runSaga(SagaDefinition $sagaDefinition): void
    {
        $completed = [];

        try {
            foreach ($sagaDefinition->getSteps() as $step) {
                ($step->action)();
                $completed[] = $step;
            }
        } catch (Throwable $throwable) {
            foreach (array_reverse($completed) as $step) {
                if ($step->compensation) {
                    ($step->compensation)();
                }
            }

            throw $throwable;
        }
    }

    /**
     * Start a new saga with the given name and context.
     *
     * @param  string  $sagaName  The name of the saga to start
     * @param  mixed  $context  The initial context/data for the saga
     * @return SagaResult The result of the saga execution
     */
    public function start(string $sagaName, mixed $context = []): SagaResult
    {
        $saga = Saga::define($sagaName)->withStore($this->sagaStore);

        // Store the saga so it can be resumed later
        $this->sagaStore->save($saga);

        return $saga->execute($context);
    }

    /**
     * Resume a saga from stored state.
     *
     * @param  string  $sagaId  The ID of the saga to resume
     * @return SagaResult The result of the saga execution
     *
     * @throws RuntimeException If the saga is not found
     */
    public function resume(string $sagaId): SagaResult
    {
        $saga = $this->sagaStore->findById($sagaId);

        if (! $saga instanceof Saga) {
            throw new RuntimeException(
                sprintf('Saga with ID "%s" not found', $sagaId),
            );
        }

        // Only running or failed sagas can be resumed
        $sagaState = $saga->getStatus();
        if ($sagaState !== SagaState::Running && $sagaState !== SagaState::Failed) {
            throw new RuntimeException(
                sprintf('Cannot resume saga in "%s" state', $sagaState->value),
            );
        }

        // Re-execute from the current state
        return $saga->execute($saga->getContext());
    }

    /**
     * Cancel a saga by running compensation.
     *
     * @param  string  $sagaId  The ID of the saga to cancel
     * @return SagaResult The result of the compensation
     *
     * @throws RuntimeException If the saga is not found
     */
    public function cancel(string $sagaId): SagaResult
    {
        $saga = $this->sagaStore->findById($sagaId);

        if (! $saga instanceof Saga) {
            throw new RuntimeException(
                sprintf('Saga with ID "%s" not found', $sagaId),
            );
        }

        return $saga->compensate();
    }

    /**
     * Get the saga store.
     */
    public function store(): SagaStoreInterface
    {
        return $this->sagaStore;
    }
}
