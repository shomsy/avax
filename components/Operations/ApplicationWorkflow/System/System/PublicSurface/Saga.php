<?php

declare(strict_types=1);

namespace Avax\Components\Operations\ApplicationWorkflow\System\System\PublicSurface;

use Avax\Components\Operations\ApplicationWorkflow\System\System\Capabilities\Compensation\CompensationExecutor;
use Avax\Components\Operations\ApplicationWorkflow\System\System\Capabilities\Idempotency\IdempotencyKey;
use Avax\Components\Operations\ApplicationWorkflow\System\System\Capabilities\SagaState\SagaState;
use Avax\Components\Operations\ApplicationWorkflow\System\System\Capabilities\SagaState\SagaStep;
use Avax\Components\Operations\ApplicationWorkflow\System\System\Capabilities\SagaStore\InMemorySagaStore;
use Avax\Components\Operations\ApplicationWorkflow\System\System\Capabilities\SagaStore\SagaStoreInterface;
use Avax\Components\Operations\ApplicationWorkflow\System\System\Capabilities\StepRunner\StepRunner;
use Closure;
use Throwable;

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
    private array $steps = [];
    private readonly string $id;
    private SagaState $sagaState = SagaState::Running;
    /**
     * @var array<string, mixed>
     */
    private array $context = [];
    private SagaStoreInterface $sagaStore;

    private StepRunner $stepRunner;

    private CompensationExecutor $compensationExecutor;

    private function __construct(private readonly string $name)
    {
        $this->id                   = $this->generateId();
        $this->sagaStore            = new InMemorySagaStore();
        $this->stepRunner           = new StepRunner();
        $this->compensationExecutor = new CompensationExecutor();
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
    public static function fromStore(SagaStoreInterface $sagaStore, string $sagaId) : ?self
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
    public function step(string $name, Closure $action, ?Closure $compensation = null) : self
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
        $this->sagaState = SagaState::Failed;
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

        $compensationResult = $this->compensationExecutor->execute(
            steps         : $this->steps,
            completedSteps: array_keys($this->stepResults),
            context       : $this->context,
        );

        if ($compensationResult->success) {
            $this->sagaState = SagaState::Compensated;
        } else {
            $this->sagaState = SagaState::Failed;
        }

        $this->sagaStore->save($this);

        return new SagaResult(
            success       : $compensationResult->success,
            sagaId        : $this->id,
            data          : $this->context,
            completedSteps: array_keys($this->stepResults),
            stepResults   : $this->stepResults,
            failureReason : $compensationResult->failureReason,
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

        try {
            foreach ($this->steps as $index => $step) {
                $idempotencyKey = IdempotencyKey::generate($this->id, $step->name, (string) $index);

                $result = $this->stepRunner->execute(
                    context       : $this->context,
                    idempotencyKey: $idempotencyKey,
                    step          : $step,
                );

                $this->stepResults[$step->name] = $result;

                // Update context with step result for next steps
                if (is_array($result)) {
                    $this->context = array_merge($this->context, $result);
                }
            }

            $this->sagaState = SagaState::Completed;
            $this->sagaStore->save($this);

            return new SagaResult(
                success       : true,
                sagaId        : $this->id,
                data          : $this->context,
                completedSteps: $this->completedSteps,
                stepResults   : $this->stepResults,
            );
        } catch (Throwable $throwable) {
            return $this->handleFailure($throwable);
        }
    }

    /**
     * Handle a failure during saga execution.
     */
    private function handleFailure(Throwable $throwable) : SagaResult
    {
        $this->sagaState     = SagaState::Failed;
        $this->failureReason = $throwable->getMessage();

        // Run compensation for completed steps in reverse order
        $compensationResult = $this->compensationExecutor->execute(
            steps         : $this->steps,
            completedSteps: $this->completedSteps,
            context       : $this->context,
        );

        if ($compensationResult->success) {
            $this->sagaState = SagaState::Compensated;
        }

        $this->sagaStore->save($this);

        return new SagaResult(
            success       : false,
            sagaId        : $this->id,
            data          : $this->context,
            completedSteps: $this->completedSteps,
            stepResults   : $this->stepResults,
            failureReason : $throwable->getMessage(),
        );
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
        return $this->completedSteps;
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
    public function getFailureReason() : ?string
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
        $this->sagaStore            = $sagaStore;
        $this->stepRunner           = new StepRunner();
        $this->compensationExecutor = new CompensationExecutor();

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
        public ?string $failureReason = null,
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
    public function getFailureReason() : ?string
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
