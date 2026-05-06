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
        private StoreSagaState $storeSagaState,
        private InspectSaga $inspectSaga,
        private ChooseCompensationSteps $chooseCompensationSteps,
    ) {
    }

    public function compensate(
        SagaInstance $sagaInstance,
        SagaDefinition $sagaDefinition,
        callable $compensationRunner,
    ): SagaInstance {
        if ($sagaInstance->status === SagaInstanceStatus::COMPENSATED) {
            return $sagaInstance;
        }

        if ($sagaInstance->status !== SagaInstanceStatus::FAILED && $sagaInstance->status !== SagaInstanceStatus::COMPENSATING) {
            throw new SagaCompensationFailure(
                message: sprintf('Saga %s is not in failed state, cannot compensate.', $sagaInstance->id),
            );
        }

        $compensationSteps = $this->chooseCompensationSteps->choose(saga: $sagaInstance, definition: $sagaDefinition);

        $results = [];
        foreach ($compensationSteps as $compensationStep) {
            $stepName = $compensationStep->name;
            $previousResult = $sagaInstance->stepResults[$stepName] ?? null;

            try {
                $compensationResult = $compensationRunner($compensationStep, $previousResult);
                $results[$stepName] = CompensationStepResult::success($stepName, $compensationResult);
            } catch (Throwable $e) {
                $results[$stepName] = CompensationStepResult::failure(stepName: $stepName, error: $e->getMessage());
            }
        }

        if ($this->hasFailedCompensations(results: $results)) {
            return $this->markAsUnrecoverable(instance: $sagaInstance);
        }

        $compensated = $sagaInstance->compensate(compensationResults: $results);
        $this->storeSagaState->save(instance: $compensated);

        $this->inspectSaga->record(
            event: SagaRuntimeEvent::compensated(
                sagaId  : $sagaInstance->id,
                sagaName: $sagaInstance->definitionName,
            ),
        );

        return $compensated;
    }

    private function hasFailedCompensations(array $results): bool
    {
        return array_any($results, fn ($result): bool => ! $result->success);
    }

    private function markAsUnrecoverable(SagaInstance $sagaInstance): SagaInstance
    {
        return $sagaInstance->fail(error: 'COMPENSATION_FAILED');
    }
}

final readonly class CompensationStepResult
{
    public array $output;

    public float $durationMs;

    private function __construct(
        public string $stepName,
        public bool $success,
        ?array $output = null,
        public ?string $error = null,
        ?float $durationMs = null,
        public ?DateTimeImmutable $completedAt = null,
    ) {
        $output ??= [];
        $durationMs ??= 0.0;
        $this->output = $output;
        $this->durationMs = $durationMs;
    }

    public static function success(
        string $stepName,
        ?array $output = null,
        float $durationMs = 0.0,
    ): self {
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
    ): self {
        return new self(
            stepName   : $stepName,
            success    : false,
            error      : $error,
            durationMs : $durationMs,
            completedAt: new DateTimeImmutable(),
        );
    }

    public function toArray(): array
    {
        return [
            'step_name' => $this->stepName,
            'success' => $this->success,
            'output' => $this->output,
            'error' => $this->error,
            'duration_ms' => $this->durationMs,
            'completed_at' => $this->completedAt?->format(format: DateTimeInterface::ISO8601),
        ];
    }
}

final readonly class CompensationPlan
{
    private function __construct(public string $sagaId, public array $steps, public ?string $failedOnStep, public bool $isRecoverable)
    {
    }

    public static function create(
        string $sagaId,
        array $steps,
        ?string $failedOnStep,
    ): self {
        return new self(
            sagaId       : $sagaId,
            steps        : $steps,
            failedOnStep : $failedOnStep,
            isRecoverable: $steps !== [],
        );
    }

    public function toArray(): array
    {
        return [
            'saga_id' => $this->sagaId,
            'steps' => array_map(
                static fn ($step) => $step->toArray(),
                $this->steps,
            ),
            'failed_on_step' => $this->failedOnStep,
            'is_recoverable' => $this->isRecoverable,
        ];
    }
}
