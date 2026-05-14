<?php

declare(strict_types=1);

namespace Avax\Components\Operations\ApplicationWorkflow\System\Capabilities\SagaExecution;

use Avax\Components\Operations\ApplicationWorkflow\System\Capabilities\Compensation\CompensationExecutor;
use Avax\Components\Operations\ApplicationWorkflow\System\Capabilities\Idempotency\IdempotencyKey;
use Avax\Components\Operations\ApplicationWorkflow\System\Capabilities\Idempotency\IdempotencyStore;
use Avax\Components\Operations\ApplicationWorkflow\System\Capabilities\Saga\Store\SagaStoreInterface;
use Avax\Components\Operations\ApplicationWorkflow\System\Capabilities\SagaState\SagaState;
use Avax\Components\Operations\ApplicationWorkflow\System\Capabilities\SagaState\SagaStep;
use Avax\Components\Operations\ApplicationWorkflow\System\Capabilities\SagaStore\InMemorySagaStore;
use Avax\Components\Operations\ApplicationWorkflow\System\Capabilities\StepRunner\StepRunner;
use Avax\Components\Operations\ApplicationWorkflow\System\PublicSurface\SagaResult;
use Closure;
use Throwable;

final class SagaExecution
{
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

    /**
     * @var array<string, mixed>
     */
    private array $stepResults = [];

    /**
     * @var array<int, string>
     */
    private array $completedSteps = [];

    private string|null $failureReason = null;

    private SagaStoreInterface $sagaStore;

    private StepRunner $stepRunner;

    private CompensationExecutor $compensationExecutor;

    private function __construct(private readonly string $name)
    {
        $this->id                   = $this->generateId();
        $this->sagaStore            = new InMemorySagaStore();
        $this->stepRunner = new StepRunner(idempotencyStore: new IdempotencyStore());
        $this->compensationExecutor = new CompensationExecutor();
    }

    private function generateId() : string
    {
        return sprintf(
            '%s-%s-%s',
            $this->name,
            date('YmdHis'),
            bin2hex(random_bytes(4)),
        );
    }

    public static function define(string $name) : self
    {
        return new self($name);
    }

    public function addStep(string $name, Closure $action, Closure|null $compensation = null) : void
    {
        $this->steps[] = new SagaStep(
            name        : $name,
            action      : $action,
            compensation: $compensation,
        );
    }

    public function fail(string $reason) : SagaResult
    {
        $this->sagaState     = SagaState::Failed;
        $this->failureReason = $reason;

        return new SagaResult(
            success       : false,
            sagaId        : $this->id,
            data          : $this->context,
            completedSteps: $this->completedSteps,
            stepResults   : $this->stepResults,
            failureReason : $reason,
        );
    }

    public function compensate() : SagaResult
    {
        $this->sagaState = SagaState::Compensating;

        $compensationResult = $this->compensationExecutor->execute(
            steps         : $this->steps,
            completedSteps: $this->completedSteps,
            context       : $this->context,
        );

        if ($compensationResult->success) {
            $this->sagaState = SagaState::Compensated;
        } else {
            $this->sagaState     = SagaState::Failed;
            $this->failureReason = $compensationResult->failureReason;
        }

        return new SagaResult(
            success       : $compensationResult->success,
            sagaId        : $this->id,
            data          : $this->context,
            completedSteps: $this->completedSteps,
            stepResults   : $this->stepResults,
            failureReason : $compensationResult->failureReason,
        );
    }

    public function execute(mixed $context = []) : SagaResult
    {
        $this->context = is_array($context) ? $context : ['value' => $context];

        try {
            foreach ($this->steps as $index => $step) {
                $idempotencyKey = IdempotencyKey::generate($this->id, $step->name, (string) $index);

                $result = $this->stepRunner->execute(
                    context       : $this->context,
                    idempotencyKey: $idempotencyKey,
                    sagaStep      : $step,
                );

                $this->stepResults[$step->name] = $result;
                $this->completedSteps[]         = $step->name;

                if (is_array($result)) {
                    $this->context = array_merge($this->context, $result);
                }
            }

            $this->sagaState = SagaState::Completed;

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

    private function handleFailure(Throwable $throwable) : SagaResult
    {
        $this->sagaState     = SagaState::Failed;
        $this->failureReason = $throwable->getMessage();

        $compensationResult = $this->compensationExecutor->execute(
            steps         : $this->steps,
            completedSteps: $this->completedSteps,
            context       : $this->context,
        );

        if ($compensationResult->success) {
            $this->sagaState = SagaState::Compensated;
        }

        return new SagaResult(
            success       : false,
            sagaId        : $this->id,
            data          : $this->context,
            completedSteps: $this->completedSteps,
            stepResults   : $this->stepResults,
            failureReason : $throwable->getMessage(),
        );
    }

    public function getId() : string
    {
        return $this->id;
    }

    public function getStatus() : SagaState
    {
        return $this->sagaState;
    }

    public function setStatus(SagaState $sagaState) : void
    {
        $this->sagaState = $sagaState;
    }

    public function getName() : string
    {
        return $this->name;
    }

    /**
     * @return array<string, mixed>
     */
    public function getContext() : array
    {
        return $this->context;
    }

    /**
     * @return array<int, string>
     */
    public function getCompletedSteps() : array
    {
        return $this->completedSteps;
    }

    /**
     * @return array<string, mixed>
     */
    public function getStepResults() : array
    {
        return $this->stepResults;
    }

    public function getFailureReason() : string|null
    {
        return $this->failureReason;
    }

    /**
     * @return array<SagaStep>
     */
    public function getSteps() : array
    {
        return $this->steps;
    }

    public function useStore(SagaStoreInterface $sagaStore) : void
    {
        $this->sagaStore            = $sagaStore;
        $this->stepRunner = new StepRunner(idempotencyStore: new IdempotencyStore());
        $this->compensationExecutor = new CompensationExecutor();
    }

    public function getStore() : SagaStoreInterface
    {
        return $this->sagaStore;
    }
}
