<?php

declare(strict_types=1);

namespace components\ApplicationWorkflow\Saga\CompleteSaga;

/**
 * PublishSagaCompleted - marks the completion event as ready for an outbox boundary.
 */
final readonly class PublishSagaCompleted
{
    public function publish(SagaCompletion $completion) : SagaCompletion
    {
        return $completion;
    }
}
