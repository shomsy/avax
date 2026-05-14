<?php

declare(strict_types=1);

namespace Avax\Components\Operations\ApplicationWorkflow\System\PublicSurface;

use Avax\Components\Operations\ApplicationWorkflow\System\Capabilities\SagaOrchestration\SagaOrchestrator;
use Avax\Components\Operations\ApplicationWorkflow\System\Capabilities\SagaStore\InMemorySagaStore;
use Avax\Components\Operations\ApplicationWorkflow\System\Capabilities\SagaStore\SagaStoreInterface;
use Avax\Components\Operations\ApplicationWorkflow\System\Capabilities\StepRunner\StepRunner;
use Closure;

/**
 * Saga - Static DSL for defining and executing sagas with compensation support.
 *
 * Usage:
 *   Saga::define('order-processing')
 *       ->step('create-order', fn($ctx) => ..., fn($ctx) => /* compensation * /)
 *       ->step('charge-payment', fn($ctx) => ..., fn($ctx) => /* refund * /)
 *       ->step('send-confirmation', fn($ctx) => ...);
 */
final class Saga
{
    /**
     * @var array<string, mixed>
     */
    protected array $stepResults = [];

    /**
     * @var array<SagaStep>
     */
    private array           $steps     = [];
    private readonly string $id;
    private SagaState       $sagaState = SagaState::Running;

    /**
     * @var array<string, mixed>
     */
    private array            $context       = [];
    private string|null      $failureReason = null;
    private SagaStoreInterface $sagaStore;
    private SagaOrchestrator $orchestrator;

    private function __construct(private readonly string $name)
    {
        $this->id           = $this->generateId();
        $this->sagaStore    = new InMemorySagaStore();
        $this->orchestrator = $this->createOrchestrator($this->sagaStore);
    }

    private function createOrchestrator(SagaStoreInterface $store) : SagaOrchestrator
    {
        return new SagaOrchestrator(
            stepRunner          : new StepRunner(idempotencyStore: new IdempotencyStore()),
            compensationExecutor: new CompensationExecutor(),
            sagaStore           : $store,
        );
    }

    /**
     * Generate a unique ID for the saga instance.
     */
    private function generateId() : string
    {
        return sprintf(
            '%s-%s-%s',
            $this->name,
            date('YmdHis'),
            bin2hex(random_bytes(4)),
        );
    }

    /**
     * Start defining a new saga with a given name.
     */
    public static function define(string $name) : self
    {
        return new self($name);
    }

    /**
     * Create a saga from an existing store (for resuming).
     */
    public static function fromStore(SagaStoreInterface $sagaStore, string $sagaId) : self|null
    {
        return $sagaStore->findById($sagaId);
    }

    /**
     * Add a step to the saga definition.
     *
     * @param string       $name         The step name
     * @param Closure      $action       The action to execute
     * @param Closure|null $compensation The compensation to run on failure
     */
    public function step(string $name, Closure $action, Closure|null $compensation = null) : self
    {
        $this->steps[] = new SagaStep(
            name        : $name,
            action      : $action,
            compensation: $compensation,
        );

        return $this;
    }

    /**
     * Mark the saga as failed with a given reason.
     */
    public function fail(string $reason) : SagaResult
    {
        $this->sagaState     = SagaState::Failed;
        $this->failureReason = $reason;
        $this->sagaStore->save($this);

        return new SagaResult(
            success       : false,
            sagaId        : $this->id,
            data          : $this->context,
            completedSteps: array_keys($this->stepResults),
            stepResults   : $this->stepResults,
            failureReason : $reason,
        );
    }

    public function compensate() : SagaResult
    {
        $this->sagaState = SagaState::Compensating;

        return $this->orchestrator->compensate(
            sagaId            : $this->id,
            steps             : $this->steps,
            completedStepNames: array_keys($this->stepResults),
            context           : $this->context,
            stepResults       : $this->stepResults,
            saveStateCallback : $this->saveState(...),
        );
    }

    /**
     * Execute the saga with the given context.
     *
     * @param mixed $context The initial context/data for the saga
     *
     * @return SagaResult The result of the saga execution
     */
    public function execute(mixed $context = []) : SagaResult
    {
        $this->context = is_array($context) ? $context : ['value' => $context];

        return $this->orchestrator->execute(
            sagaId           : $this->id,
            steps            : $this->steps,
            context          : $this->context,
            saveStateCallback: $this->saveState(...),
        );
    }

    /**
     * Internal callback for orchestrator to update saga state.
     *
     * @param array<string, mixed> $context
     * @param array<string, mixed> $stepResults
     */
    private function saveState(SagaState $state, array $context, array $stepResults, string|null $failureReason = null) : void
    {
        $this->sagaState     = $state;
        $this->context       = $context;
        $this->stepResults   = $stepResults;
        $this->failureReason = $failureReason;

        $this->sagaStore->save($this);
    }

    /**
     * Get the saga's unique identifier.
     */
    public function getId() : string
    {
        return $this->id;
    }

    /**
     * Get the current saga status.
     */
    public function getStatus() : SagaState
    {
        return $this->sagaState;
    }

    /**
     * Set the saga status (used by store).
     */
    public function setStatus(SagaState $sagaState) : void
    {
        $this->sagaState = $sagaState;
    }

    /**
     * Get the saga name.
     */
    public function getName() : string
    {
        return $this->name;
    }

    /**
     * Get the execution context.
     *
     * @return array<string, mixed>
     */
    public function getContext() : array
    {
        return $this->context;
    }

    /**
     * Get completed step names.
     *
     * @return array<int, string>
     */
    public function getCompletedSteps() : array
    {
        return array_keys($this->stepResults);
    }

    /**
     * Get step results.
     *
     * @return array<string, mixed>
     */
    public function getStepResults() : array
    {
        return $this->stepResults;
    }

    /**
     * Get the failure reason if any.
     */
    public function getFailureReason() : string|null
    {
        return $this->failureReason;
    }

    /**
     * Get the saga steps.
     *
     * @return array<SagaStep>
     */
    public function getSteps() : array
    {
        return $this->steps;
    }

    /**
     * Set the store for this saga.
     */
    public function withStore(SagaStoreInterface $sagaStore) : self
    {
        $this->sagaStore    = $sagaStore;
        $this->orchestrator = $this->createOrchestrator($sagaStore);

        return $this;
    }
}

/**
 * Result of a saga execution.
 */
final readonly class SagaResult
{
    public function __construct(
        public bool    $success,
        public string  $sagaId,
        public array   $data,
        public array   $completedSteps,
        public array   $stepResults,
        public string|null $failureReason = null,
    ) {}

    /**
     * Check if the saga completed successfully.
     */
    public function isSuccessful() : bool
    {
        return $this->success;
    }

    /**
     * Get the failure reason if the saga failed.
     */
    public function getFailureReason() : string|null
    {
        return $this->failureReason;
    }

    /**
     * Get data for a specific step.
     */
    public function getStepResult(string $stepName) : mixed
    {
        return $this->stepResults[$stepName] ?? null;
    }
}
