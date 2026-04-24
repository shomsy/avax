<?php

declare(strict_types=1);

namespace Avax\ApplicationWorkflow\Saga\CompensateSaga;

use Avax\ApplicationWorkflow\Saga\StartSaga\SagaInstance;
use Avax\ApplicationWorkflow\Saga\StartSaga\SagaInstanceStatus;
use Avax\ApplicationWorkflow\Saga\DefineSaga\SagaDefinition;
use Avax\ApplicationWorkflow\Saga\DefineSaga\SagaStepDefinition;
use Avax\ApplicationWorkflow\Saga\StoreSagaState\StoreSagaState;
use Avax\ApplicationWorkflow\Saga\InspectSaga\InspectSaga;

final readonly class CompensateSaga
{
    public function __construct(
        private StoreSagaState $storeSagaState,
        private InspectSaga    $inspectSaga
    ) {}

    public function compensate(
        SagaInstance   $instance,
        SagaDefinition $definition,
        callable       $compensationRunner
    ) : SagaInstance
    {
        if ($instance->status === SagaInstanceStatus::COMPENSATED) {
            return $instance;
        }

        if ($instance->status !== SagaInstanceStatus::FAILED &&
            $instance->status !== SagaInstanceStatus::COMPENSATING) {
            throw new SagaCompensationFailure(
                sprintf('Saga %s is not in failed state, cannot compensate.', $instance->id)
            );
        }

        $compensationSteps = $this->chooseCompensationSteps($definition, $instance);

        $results = [];
        foreach ($compensationSteps as $stepName => $stepDef) {
            $previousResult = $instance->stepResults[$stepName] ?? null;

            try {
                $compensationResult = $compensationRunner($stepDef, $previousResult);
                $results[$stepName] = CompensationStepResult::success($stepName, $compensationResult);
            } catch (\Throwable $e) {
                $results[$stepName] = CompensationStepResult::failure($stepName, $e->getMessage());
            }
        }

        if ($this->hasFailedCompensations($results)) {
            return $this->markAsUnrecoverable($instance);
        }

        $compensated = $instance->compensate();
        $this->storeSagaState->save($compensated);

        $this->inspectSaga->record(
            \Avax\ApplicationWorkflow\Saga\InspectSaga\SagaRuntimeEvent::compensated(
                $instance->id,
                $instance->definitionName
            )
        );

        return $compensated;
    }

    public function chooseCompensationSteps(
        SagaDefinition $definition,
        SagaInstance   $instance
    ) : array
    {
        $steps = [];

        foreach (array_reverse($definition->stepOrder) as $stepName) {
            $stepDef = $definition->getStep($stepName);
            if ($stepDef?->hasCompensation() && $this->wasCompleted($instance, $stepName)) {
                $steps[$stepName] = $stepDef;
            }
        }

        return $steps;
    }

    private function wasCompleted(SagaInstance $instance, string $stepName) : bool
    {
        return in_array($stepName, $instance->completedSteps, true);
    }

    private function hasFailedCompensations(array $results) : bool
    {
        foreach ($results as $result) {
            if (! $result->success) {
                return true;
            }
        }

        return false;
    }

    private function markAsUnrecoverable(SagaInstance $instance) : SagaInstance
    {
        return $instance->fail('COMPENSATION_FAILED');
    }
}

final readonly class CompensationStepResult
{
    public string              $stepName;
    public bool                $success;
    public array               $output;
    public ?string             $error;
    public float               $durationMs;
    public ?\DateTimeImmutable $completedAt;

    private function __construct(
        string              $stepName,
        bool                $success,
        array               $output = [],
        ?string             $error = null,
        float               $durationMs = 0.0,
        ?\DateTimeImmutable $completedAt = null
    )
    {
        $this->stepName    = $stepName;
        $this->success     = $success;
        $this->output      = $output;
        $this->error       = $error;
        $this->durationMs  = $durationMs;
        $this->completedAt = $completedAt;
    }

    public static function success(
        string $stepName,
        array  $output = [],
        float  $durationMs = 0.0
    ) : self
    {
        return new self(
            stepName   : $stepName,
            success    : true,
            output     : $output,
            durationMs : $durationMs,
            completedAt: new \DateTimeImmutable()
        );
    }

    public static function failure(
        string $stepName,
        string $error,
        float  $durationMs = 0.0
    ) : self
    {
        return new self(
            stepName   : $stepName,
            success    : false,
            error      : $error,
            durationMs : $durationMs,
            completedAt: new \DateTimeImmutable()
        );
    }

    public function toArray() : array
    {
        return [
            'step_name'    => $this->stepName,
            'success'      => $this->success,
            'output'       => $this->output,
            'error'        => $this->error,
            'duration_ms'  => $this->durationMs,
            'completed_at' => $this->completedAt?->format(\DateTimeInterface::ISO8601),
        ];
    }
}

final readonly class CompensationPlan
{
    public string  $sagaId;
    public array   $steps;
    public ?string $failedOnStep;
    public bool    $isRecoverable;

    private function __construct(
        string  $sagaId,
        array   $steps,
        ?string $failedOnStep,
        bool    $isRecoverable
    )
    {
        $this->sagaId        = $sagaId;
        $this->steps         = $steps;
        $this->failedOnStep  = $failedOnStep;
        $this->isRecoverable = $isRecoverable;
    }

    public static function create(
        string  $sagaId,
        array   $steps,
        ?string $failedOnStep
    ) : self
    {
        return new self(
            sagaId       : $sagaId,
            steps        : $steps,
            failedOnStep : $failedOnStep,
            isRecoverable: ! empty($steps)
        );
    }

    public function toArray() : array
    {
        return [
            'saga_id'        => $this->sagaId,
            'steps'          => array_map(
                fn ($step) => $step->toArray(),
                $this->steps
            ),
            'failed_on_step' => $this->failedOnStep,
            'is_recoverable' => $this->isRecoverable,
        ];
    }
}