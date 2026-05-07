<?php

declare(strict_types=1);

namespace Avax\Components\Operations\ApplicationWorkflow\System\Flows\Saga\CompleteSaga;

use Avax\Components\Operations\ApplicationWorkflow\System\Flows\Saga\DefineSaga\SagaDefinition;
use Avax\Components\Operations\ApplicationWorkflow\System\Flows\Saga\InspectSaga\InspectSaga;
use Avax\Components\Operations\ApplicationWorkflow\System\Flows\Saga\InspectSaga\SagaRuntimeEvent;
use Avax\Components\Operations\ApplicationWorkflow\System\Flows\Saga\StartSaga\SagaInstance;
use Avax\Components\Operations\ApplicationWorkflow\System\Flows\Saga\StartSaga\SagaInstanceStatus;
use Avax\Components\Operations\ApplicationWorkflow\System\Flows\Saga\StoreSagaState\StoreSagaState;
use DateTimeImmutable;
use DateTimeInterface;

final readonly class CompleteSaga
{
    public function __construct(
        private StoreSagaState $storeSagaState,
        private InspectSaga    $inspectSaga,
    ) {}

    public function complete(SagaInstance $sagaInstance) : SagaInstance
    {
        if ($sagaInstance->status === SagaInstanceStatus::COMPLETED) {
            return $sagaInstance;
        }

        if ($sagaInstance->status === SagaInstanceStatus::FAILED) {
            throw new SagaCompletionFailure(
                message: sprintf('Cannot complete failed saga %s.', $sagaInstance->id),
            );
        }

        $completed = $sagaInstance->complete();

        $this->storeSagaState->save(instance: $completed);

        $this->inspectSaga->record(
            event: SagaRuntimeEvent::completed(
                     sagaId  : $sagaInstance->id,
                     sagaName: $sagaInstance->definitionName,
                 ),
        );

        return $completed;
    }

    public function detectCompletion(
        SagaInstance   $sagaInstance,
        SagaDefinition $sagaDefinition,
    ) : bool
    {
        if ($sagaInstance->currentStepName === null) {
            return true;
        }

        $nextStep = $sagaDefinition->getNextStep($sagaInstance->currentStepName ?? '');

        return $nextStep === null;
    }

    public function publishCompletion(SagaInstance $sagaInstance) : void
    {
        $payload = [
            'saga_id'         => $sagaInstance->id,
            'definition_name' => $sagaInstance->definitionName,
            'data'            => $sagaInstance->data,
            'completed_steps' => $sagaInstance->completedSteps,
            'completed_at'    => $sagaInstance->completedAt?->format(format: DateTimeInterface::ISO8601),
        ];

        $this->inspectSaga->record(
            event: SagaRuntimeEvent::create(
                     sagaId  : $sagaInstance->id,
                     sagaName: $sagaInstance->definitionName,
                     type    : 'saga_completed',
                     payload : $payload,
                 ),
        );
    }
}

final readonly class SagaCompletion
{
    private function __construct(public string $sagaId, public string $definitionName, public array $finalData, public array $completedSteps, public DateTimeImmutable $completedAt, public float $totalDurationMs) {}

    public static function fromInstance(
        SagaInstance $sagaInstance,
        float        $startTimeMs,
    ) : self
    {
        return new self(
            sagaId         : $sagaInstance->id,
            definitionName : $sagaInstance->definitionName,
            finalData      : $sagaInstance->data,
            completedSteps : $sagaInstance->completedSteps,
            completedAt    : new DateTimeImmutable(),
            totalDurationMs: (microtime(true) * 1000) - $startTimeMs,
        );
    }

    public function toArray() : array
    {
        return [
            'saga_id'           => $this->sagaId,
            'definition_name'   => $this->definitionName,
            'final_data'        => $this->finalData,
            'completed_steps'   => $this->completedSteps,
            'completed_at'      => $this->completedAt->format(format: DateTimeInterface::ISO8601),
            'total_duration_ms' => $this->totalDurationMs,
        ];
    }
}
