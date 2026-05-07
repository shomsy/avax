<?php

declare(strict_types=1);

namespace Avax\Components\Operations\ApplicationWorkflow\System\Flows\Saga\CompleteSaga;

use Avax\Components\Operations\ApplicationWorkflow\System\Flows\Saga\InspectSaga\SagaRuntimeEvent;
use Avax\Components\Operations\ApplicationWorkflow\System\Flows\Saga\StartSaga\SagaInstance;
use DateTimeInterface;
use RuntimeException;
use Throwable;

final readonly class DetectSagaCompletion
{
    public function isComplete(
        SagaInstance $sagaInstance,
        array        $definition,
    ) : bool
    {
        if ($sagaInstance->currentStepName === null) {
            return true;
        }

        $currentStep = $sagaInstance->currentStepIndex;
        $totalSteps  = count($definition['stepOrder'] ?? []);

        return $currentStep >= $totalSteps - 1;
    }

    public function getNextStep(
        SagaInstance $sagaInstance,
        array        $definition,
    ) : ?string
    {
        $nextIndex = $sagaInstance->currentStepIndex + 1;
        $stepOrder = $definition['stepOrder'] ?? [];

        if ($nextIndex >= count($stepOrder)) {
            return null;
        }

        return $stepOrder[$nextIndex] ?? null;
    }

    public function remainingSteps(
        SagaInstance $sagaInstance,
        array        $definition,
    ) : int
    {
        $total = count($definition['stepOrder'] ?? []);

        return max(0, $total - $sagaInstance->currentStepIndex - 1);
    }
}

final readonly class RecordSagaCompleted
{
    public function __construct(private object $store, private object $inspect) {}

    public function record(SagaInstance $sagaInstance) : void
    {
        $completed = $sagaInstance->complete();
        $this->store->set('saga_' . $completed->id, $completed->toArray());

        $this->inspect->record(
            SagaRuntimeEvent::completed(
                sagaId  : $completed->id,
                sagaName: $completed->definitionName,
            ),
        );
    }
}

final readonly class PublishSagaCompleted
{
    public function __construct(private object $messageBus) {}

    public function publish(SagaInstance $sagaInstance) : void
    {
        $topic = sprintf('saga.%s.completed', $sagaInstance->definitionName);

        $this->messageBus->publish($topic, [
            'saga_id'         => $sagaInstance->id,
            'definition_name' => $sagaInstance->definitionName,
            'final_data'      => $sagaInstance->data,
            'completed_steps' => $sagaInstance->completedSteps,
            'correlation_id'  => $sagaInstance->correlationId,
            'tenant_id'       => $sagaInstance->tenantId,
            'completed_at'    => $sagaInstance->completedAt?->format(format: DateTimeInterface::ISO8601),
        ]);
    }
}

final class SagaCompletionFailure extends RuntimeException
{
    public function __construct(string $message = 'Saga completion failed.', ?Throwable $previous = null)
    {
        parent::__construct(message: $message, previous: $previous);
    }
}
