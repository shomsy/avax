<?php

declare(strict_types=1);

namespace Avax\Components\Operations\ApplicationWorkflow\System\PublicSurface;

use Avax\Components\Operations\ApplicationWorkflow\System\Capabilities\Saga\SagaDefinition;
use Avax\Components\Operations\ApplicationWorkflow\System\Capabilities\SagaExecutor\SagaExecutor;
use Avax\Components\Operations\ApplicationWorkflow\System\Capabilities\SagaLifecycle\ValidateSagaTransition;
use Avax\Components\Operations\ApplicationWorkflow\System\Capabilities\SagaStore\SagaStoreInterface;
use RuntimeException;

/**
 * Workflow facade for saga orchestration.
 *
 * Provides high-level operations for starting, resuming, and canceling sagas.
 * Delegates saga execution to the SagaExecutor capability.
 */
final readonly class Workflow
{
    public function __construct(
        private SagaStoreInterface   $sagaStore,
        private SagaExecutor         $sagaExecutor,
        private ValidateSagaTransition $validateSagaTransition,
    ) {
    }

    /**
     * Run a saga definition (legacy compatibility method).
     *
     * Delegates execution to SagaExecutor which handles compensation on failure.
     *
     * @return list<string> Names of successfully completed steps
     */
    public function runSaga(SagaDefinition $sagaDefinition): array
    {
        return $this->sagaExecutor->execute($sagaDefinition);
    }

    /**
     * Start a new saga with the given name and context.
     *
     * @param string $sagaName The name of the saga to start
     * @param mixed  $context  The initial context/data for the saga
     *
     * @return SagaResult The result of the saga execution
     */
    public function start(string $sagaName, mixed $context = []) : SagaResult
    {
        $saga = Saga::define($sagaName)->withStore($this->sagaStore);

        // Store the saga so it can be resumed later
        $this->sagaStore->save($saga);

        return $saga->execute($context);
    }

    /**
     * Resume a saga from stored state.
     *
     * @param string $sagaId The ID of the saga to resume
     *
     * @return SagaResult The result of the saga execution
     *
     * @throws RuntimeException If the saga is not found
     */
    public function resume(string $sagaId) : SagaResult
    {
        $saga = $this->sagaStore->findById($sagaId);

        if (! $saga instanceof Saga) {
            throw new RuntimeException(
                sprintf('Saga with ID "%s" not found', $sagaId),
            );
        }

        $this->validateSagaTransition->assertResumable(saga: $saga, sagaId: $sagaId);

        // Re-execute from the current state
        return $saga->execute($saga->getContext());
    }

    /**
     * Cancel a saga by running compensation.
     *
     * @param string $sagaId The ID of the saga to cancel
     *
     * @return SagaResult The result of the compensation
     *
     * @throws RuntimeException If the saga is not found
     */
    public function cancel(string $sagaId) : SagaResult
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
    public function store() : SagaStoreInterface
    {
        return $this->sagaStore;
    }
}
