<?php

declare(strict_types=1);

namespace Avax\ApplicationWorkflow\Saga\CompleteSaga;

use Avax\ApplicationWorkflow\Saga\DefineSaga\SagaDefinition;
use Avax\ApplicationWorkflow\Saga\InspectSaga\InspectSaga;
use Avax\ApplicationWorkflow\Saga\StartSaga\SagaInstance;
use Avax\ApplicationWorkflow\Saga\StartSaga\SagaInstanceStatus;
use Avax\ApplicationWorkflow\Saga\StoreSagaState\StoreSagaState;

final readonly class CompleteSaga
{
    public function __construct(
        private StoreSagaState $storeSagaState,
        private InspectSaga    $inspectSaga
    ) {}

    public function complete(SagaInstance $instance) : SagaInstance
    {
        if ($instance->status === SagaInstanceStatus::COMPLETED) {
            return $instance;
        }

        if ($instance->status === SagaInstanceStatus::FAILED) {
            throw new SagaCompletionFailure(
                sprintf('Cannot complete failed saga %s.', $instance->id)
            );
        }

        $completed = $instance->complete();

        $this->storeSagaState->save($completed);

        $this->inspectSaga->record(
            \Avax\ApplicationWorkflow\Saga\InspectSaga\SagaRuntimeEvent::completed(
                $instance->id,
                $instance->definitionName
            )
        );

        return $completed;
    }

    public function detectCompletion(
        SagaInstance   $instance,
        SagaDefinition $definition
    ) : bool
    {
        if ($instance->currentStepName === null) {
            return true;
        }

        $nextStep = $definition->getNextStep($instance->currentStepName ?? '');

        return $nextStep === null;
    }

    public function publishCompletion(SagaInstance $instance) : void
    {
        $payload = [
            'saga_id'         => $instance->id,
            'definition_name' => $instance->definitionName,
            'data'            => $instance->data,
            'completed_steps' => $instance->completedSteps,
            'completed_at'    => $instance->completedAt?->format(\DateTimeInterface::ISO8601),
        ];

        $this->inspectSaga->record(
            \Avax\ApplicationWorkflow\Saga\InspectSaga\SagaRuntimeEvent::create(
                $instance->id,
                $instance->definitionName,
                'saga_completed',
                $payload
            )
        );
    }
}

final readonly class SagaCompletion
{
    public string             $sagaId;
    public string             $definitionName;
    public array              $finalData;
    public array              $completedSteps;
    public \DateTimeImmutable $completedAt;
    public float              $totalDurationMs;

    private function __construct(
        string             $sagaId,
        string             $definitionName,
        array              $finalData,
        array              $completedSteps,
        \DateTimeImmutable $completedAt,
        float              $totalDurationMs
    )
    {
        $this->sagaId          = $sagaId;
        $this->definitionName  = $definitionName;
        $this->finalData       = $finalData;
        $this->completedSteps  = $completedSteps;
        $this->completedAt     = $completedAt;
        $this->totalDurationMs = $totalDurationMs;
    }

    public static function fromInstance(
        SagaInstance $instance,
        float        $startTimeMs
    ) : self
    {
        return new self(
            sagaId         : $instance->id,
            definitionName : $instance->definitionName,
            finalData      : $instance->data,
            completedSteps : $instance->completedSteps,
            completedAt    : new \DateTimeImmutable(),
            totalDurationMs: (microtime(true) * 1000) - $startTimeMs
        );
    }

    public function toArray() : array
    {
        return [
            'saga_id'           => $this->sagaId,
            'definition_name'   => $this->definitionName,
            'final_data'        => $this->finalData,
            'completed_steps'   => $this->completedSteps,
            'completed_at'      => $this->completedAt->format(\DateTimeInterface::ISO8601),
            'total_duration_ms' => $this->totalDurationMs,
        ];
    }
}