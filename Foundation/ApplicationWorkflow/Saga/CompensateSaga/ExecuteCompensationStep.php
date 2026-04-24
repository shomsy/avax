<?php

declare(strict_types=1);

namespace Avax\ApplicationWorkflow\Saga\CompensateSaga;

use Avax\ApplicationWorkflow\Saga\DefineSaga\SagaStepDefinition;

final readonly class ExecuteCompensationStep
{
    public function __construct(private object $compensationRunner) {}

    public function execute(
        SagaStepDefinition $step,
        array              $previousResult
    ) : array
    {
        if ($step->compensationComponent === null) {
            throw new SagaCompensationFailure(
                sprintf('No compensation defined for step %s.', $step->name)
            );
        }

        $startTime = microtime(true);

        $input  = $previousResult['output'] ?? [];
        $result = ($this->compensationRunner)($step->compensationComponent, $input);

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
    public function __construct(private object $store, private object $inspect) {}

    public function record(
        string $sagaId,
        string $stepName,
        array  $result
    ) : void
    {
        $this->inspect->record(
            \Avax\ApplicationWorkflow\Saga\InspectSaga\SagaRuntimeEvent::create(
                sagaId  : $sagaId,
                sagaName: '',
                type    : 'compensation_completed',
                payload : $result,
                stepName: $stepName
            )
        );
    }
}

final readonly class RecordCompensationFailed
{
    public function __construct(private object $store, private object $inspect) {}

    public function record(
        string $sagaId,
        string $stepName,
        string $error
    ) : void
    {
        $this->inspect->record(
            \Avax\ApplicationWorkflow\Saga\InspectSaga\SagaRuntimeEvent::create(
                sagaId  : $sagaId,
                sagaName: '',
                type    : 'compensation_failed',
                payload : ['error' => $error],
                stepName: $stepName
            )
        );
    }
}

final readonly class PublishSagaCompensated
{
    public function __construct(private object $messageBus) {}

    public function publish(
        string $sagaId,
        string $definitionName,
        array  $compensationResults
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

class SagaCompensationFailure extends \RuntimeException
{
    public function __construct(string $message = 'Saga compensation failed.', ?\Throwable $previous = null)
    {
        parent::__construct($message, previous: $previous);
    }
}