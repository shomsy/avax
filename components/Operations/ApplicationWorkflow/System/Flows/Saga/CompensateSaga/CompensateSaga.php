<?php

declare(strict_types=1);

namespace Avax\Components\Operations\ApplicationWorkflow\System\Flows\Saga\CompensateSaga;

use Avax\Components\Operations\ApplicationWorkflow\System\Flows\Saga\DefineSaga\SagaDefinition;
use Avax\Components\Operations\ApplicationWorkflow\System\Flows\Saga\InspectSaga\InspectSaga;
use Avax\Components\Operations\ApplicationWorkflow\System\Flows\Saga\InspectSaga\SagaRuntimeEvent;
use Avax\Components\Operations\ApplicationWorkflow\System\Flows\Saga\StartSaga\SagaInstance;
use Avax\Components\Operations\ApplicationWorkflow\System\Flows\Saga\StartSaga\SagaInstanceStatus;
use Avax\Components\Operations\ApplicationWorkflow\System\Flows\Saga\StoreSagaState\StoreSagaState;
use DateTimeImmutable;
use DateTimeInterface;
use Throwable;

final readonly class CompensateSaga
{
    public function __construct(
        private StoreSagaState          $storeSagaState,
        private InspectSaga             $inspectSaga,
        private ChooseCompensationSteps $chooseCompensationSteps,
    ) {}

    public function compensate(
        SagaInstance $instance,
        SagaDefinition $definition,
        callable     $compensationRunner,
    ) : SagaInstance
    {
        if ($instance->status === SagaInstanceStatus::COMPENSATED) {
            return $instance;
        }

        if ($instance->status !== SagaInstanceStatus::FAILED && $instance->status !== SagaInstanceStatus::COMPENSATING) {
            throw new SagaCompensationFailure(
                message: sprintf('Saga %s is not in failed state, cannot compensate.', $instance->id),
            );
        }

        $compensationSteps = $this->chooseCompensationSteps->choose(saga: $instance, definition: $definition);

        $results = [];
        foreach ($compensationSteps as $stepDef) {
            $stepName       = $stepDef->name;
            $previousResult = $instance->stepResults[$stepName] ?? null;

            try {
                $compensationResult = $compensationRunner($stepDef, $previousResult);
                $results[$stepName] = CompensationStepResult::success($stepName, $compensationResult);
            } catch (Throwable $e) {
                $results[$stepName] = CompensationStepResult::failure(stepName: $stepName, error: $e->getMessage());
            }
        }

        if ($this->hasFailedCompensations(results: $results)) {
            return $this->markAsUnrecoverable(instance: $instance);
        }

        $compensated = $instance->compensate(compensationResults: $results);
        $this->storeSagaState->save(instance: $compensated);

        $this->inspectSaga->record(
            event: SagaRuntimeEvent::compensated(
                     sagaId  : $instance->id,
                     sagaName: $instance->definitionName,
                 ),
        );

        return $compensated;
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
        return $instance->fail(error: 'COMPENSATION_FAILED');
    }

    private function wasCompleted(SagaInstance $instance, string $stepName) : bool
    {
        return in_array($stepName, $instance->completedSteps, true);
    }
}

final readonly class CompensationStepResult
{
    public string      $stepName;
    public bool        $success;
    public array       $output;
    public string|null $error;
    public float       $durationMs;
    public DateTimeImmutable|null $completedAt;

    private function __construct(
        string            $stepName,
        bool              $success,
        array             $output = null,
        string            $error = null,
        float             $durationMs = null,
        DateTimeImmutable $completedAt = null,
    )
    {
        $output     ??= [];
        $durationMs ??= 0.0;
        $this->stepName    = $stepName;
        $this->success     = $success;
        $this->output      = $output;
        $this->error       = $error;
        $this->durationMs  = $durationMs;
        $this->completedAt = $completedAt;
    }

    public static function success(
        string $stepName,
        array  $output = null,
        float  $durationMs = 0.0,
    ) : self
    {
        $output ??= [];

        return new self(
            stepName   : $stepName,
            success    : true,
            output     : $output,
            durationMs : $durationMs,
            completedAt: new DateTimeImmutable(),
        );
    }

    public static function failure(
        string $stepName,
        string $error,
        float $durationMs = 0.0,
    ) : self
    {
        return new self(
            stepName   : $stepName,
            success    : false,
            error      : $error,
            durationMs : $durationMs,
            completedAt: new DateTimeImmutable(),
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
            'completed_at' => $this->completedAt?->format(format: DateTimeInterface::ISO8601),
        ];
    }
}

final readonly class CompensationPlan
{
    public string $sagaId;
    public array  $steps;
    public string|null $failedOnStep;
    public bool   $isRecoverable;

    private function __construct(
        string $sagaId,
        array  $steps,
        string|null $failedOnStep,
        bool   $isRecoverable,
    )
    {
        $this->sagaId        = $sagaId;
        $this->steps         = $steps;
        $this->failedOnStep  = $failedOnStep;
        $this->isRecoverable = $isRecoverable;
    }

    public static function create(
        string      $sagaId,
        array       $steps,
        string|null $failedOnStep,
    ) : self
    {
        return new self(
            sagaId       : $sagaId,
            steps        : $steps,
            failedOnStep : $failedOnStep,
            isRecoverable: ! empty($steps),
        );
    }

    public function toArray() : array
    {
        return [
            'saga_id' => $this->sagaId,
            'steps'   => array_map(
                static fn ($step) => $step->toArray(),
                $this->steps,
            ),
            'failed_on_step' => $this->failedOnStep,
            'is_recoverable' => $this->isRecoverable,
        ];
    }
}
