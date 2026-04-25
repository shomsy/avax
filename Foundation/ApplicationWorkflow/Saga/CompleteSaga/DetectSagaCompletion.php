<?php

declare(strict_types=1);

namespace Avax\ApplicationWorkflow\Saga\CompleteSaga;

use Avax\ApplicationWorkflow\Saga\InspectSaga\SagaRuntimeEvent;
use Avax\ApplicationWorkflow\Saga\StartSaga\SagaInstance;
use DateTimeInterface;
use RuntimeException;
use Throwable;

final readonly class DetectSagaCompletion
{
    public function isComplete(
        SagaInstance $instance,
        array        $definition
    ) : bool
    {
        if ($instance->currentStepName === null) {
            return true;
        }

        $currentStep = $instance->currentStepIndex;
        $totalSteps  = count($definition['stepOrder'] ?? []);

        return $currentStep >= $totalSteps - 1;
    }

    public function getNextStep(
        SagaInstance $instance,
        array        $definition
    ) : string|null
    {
        $nextIndex = $instance->currentStepIndex + 1;
        $stepOrder = $definition['stepOrder'] ?? [];

        if ($nextIndex >= count($stepOrder)) {
            return null;
        }

        return $stepOrder[$nextIndex] ?? null;
    }

    public function remainingSteps(
        SagaInstance $instance,
        array        $definition
    ) : int
    {
        $total = count($definition['stepOrder'] ?? []);

        return max(0, $total - $instance->currentStepIndex - 1);
    }
}

final readonly class RecordSagaCompleted
{
    public function __construct(private object $store, private object $inspect) {}

    public function record(SagaInstance $instance) : void
    {
        $completed = $instance->complete();
        $this->store->set("saga_{$completed->id}", $completed->toArray());

        $this->inspect->record(
            SagaRuntimeEvent::completed(
                sagaId  : $completed->id,
                sagaName: $completed->definitionName
            )
        );
    }
}

final readonly class PublishSagaCompleted
{
    public function __construct(private object $messageBus) {}

    public function publish(SagaInstance $instance) : void
    {
        $topic = sprintf('saga.%s.completed', $instance->definitionName);

        $this->messageBus->publish($topic, [
            'saga_id'         => $instance->id,
            'definition_name' => $instance->definitionName,
            'final_data'      => $instance->data,
            'completed_steps' => $instance->completedSteps,
            'correlation_id'  => $instance->correlationId,
            'tenant_id'       => $instance->tenantId,
            'completed_at' => $instance->completedAt?->format(format: DateTimeInterface::ISO8601),
        ]);
    }
}

final class SagaCompletionFailure extends RuntimeException
{
    public function __construct(string $message = 'Saga completion failed.', Throwable|null $previous = null)
    {
        parent::__construct(message: $message, previous: $previous);
    }
}