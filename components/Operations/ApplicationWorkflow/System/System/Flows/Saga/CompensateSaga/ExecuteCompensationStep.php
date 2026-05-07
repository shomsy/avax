<?php

declare(strict_types=1);

namespace Avax\Components\Operations\ApplicationWorkflow\System\System\Flows\Saga\CompensateSaga;

use Avax\Components\Operations\ApplicationWorkflow\System\System\Flows\Saga\DefineSaga\SagaStepDefinition;
use Avax\Components\Operations\ApplicationWorkflow\System\System\Flows\Saga\InspectSaga\SagaRuntimeEvent;
use RuntimeException;
use Throwable;

final readonly class ExecuteCompensationStep
{
    public function __construct(private object $compensationRunner) {}

    public function execute(
        SagaStepDefinition $sagaStepDefinition,
        array              $previousResult,
    ) : array
    {
        if ($sagaStepDefinition->compensationComponent === null) {
            throw new SagaCompensationFailure(
                message: sprintf('No compensation defined for step %s.', $sagaStepDefinition->name),
            );
        }

        $startTime = microtime(true);

        $input  = $previousResult['output'] ?? [];
        $result = ($this->compensationRunner)($sagaStepDefinition->compensationComponent, $input);

        $duration = (microtime(true) - $startTime) * 1000;

        return [
            'success'     => true,
            'output'      => is_array($result) ? $result : ['result' => $result],
            'duration_ms' => $duration,
        ];
    }
}

final readonly class RecordCompensationCompleted
{
    public function __construct(private object $inspect) {}

    public function record(
        string $sagaId,
        string $stepName,
        array  $result,
    ) : void
    {
        $this->inspect->record(
            SagaRuntimeEvent::create(
                sagaId  : $sagaId,
                sagaName: '',
                type    : 'compensation_completed',
                payload : $result,
                stepName: $stepName,
            ),
        );
    }
}

final readonly class RecordCompensationFailed
{
    public function __construct(private object $inspect) {}

    public function record(
        string $sagaId,
        string $stepName,
        string $error,
    ) : void
    {
        $this->inspect->record(
            SagaRuntimeEvent::create(
                sagaId  : $sagaId,
                sagaName: '',
                type    : 'compensation_failed',
                payload : ['error' => $error],
                stepName: $stepName,
            ),
        );
    }
}

final readonly class PublishSagaCompensated
{
    public function __construct(private object $messageBus) {}

    public function publish(
        string $sagaId,
        string $definitionName,
        array  $compensationResults,
    ) : void
    {
        $topic = sprintf('saga.%s.compensated', $definitionName);

        $this->messageBus->publish($topic, [
            'saga_id'              => $sagaId,
            'definition_name'      => $definitionName,
            'compensation_results' => $compensationResults,
        ]);
    }
}

class SagaCompensationFailure extends RuntimeException
{
    public function __construct(string $message = 'Saga compensation failed.', ?Throwable $previous = null)
    {
        parent::__construct(message: $message, previous: $previous);
    }
}
