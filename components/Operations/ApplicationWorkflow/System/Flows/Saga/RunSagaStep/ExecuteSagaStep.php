<?php

declare(strict_types=1);

namespace Avax\Components\Operations\ApplicationWorkflow\System\Flows\Saga\RunSagaStep;

use Avax\Components\Operations\ApplicationWorkflow\System\Flows\Saga\DefineSaga\SagaStepDefinition;
use Avax\Components\Operations\ApplicationWorkflow\System\Flows\Saga\InspectSaga\SagaRuntimeEvent;
use Avax\Components\Operations\ApplicationWorkflow\System\Flows\Saga\StartSaga\SagaInstance;

final readonly class ExecuteSagaStep
{
    public function __construct(private object $stepRunner) {}

    public function execute(
        SagaStepDefinition $sagaStepDefinition,
        array $sagaData,
    ): array {
        $startTime = microtime(true);

        $input = $this->prepareInput(stepInput: $sagaStepDefinition->input, sagaData: $sagaData);

        $result = ($this->stepRunner)($sagaStepDefinition->component, $input);

        $duration = (microtime(true) - $startTime) * 1000;

        return [
            'success' => true,
            'output'  => is_array($result) ? $result : ['result' => $result],
            'duration_ms' => $duration,
        ];
    }

    private function prepareInput(array $stepInput, array $sagaData): array
    {
        $input = [];
        foreach ($stepInput as $key => $value) {
            if (is_string($value) && str_starts_with($value, '$')) {
                $dataKey = substr($value, 1);
                $input[$key] = $sagaData[$dataKey] ?? $value;
            } else {
                $input[$key] = $value;
            }
        }

        return $input;
    }
}

final readonly class LoadSagaInstance
{
    public function __construct(private object $store) {}

    public function load(string $sagaId): ?SagaInstance
    {
        $data = $this->store->get('saga_' . $sagaId);
        if ($data === null) {
            return null;
        }

        return SagaInstance::fromArray(row: $data);
    }

    public function exists(string $sagaId): bool
    {
        return $this->store->get('saga_' . $sagaId) !== null;
    }
}

final readonly class RecordSagaStepCompleted
{
    public function __construct(private object $store, private object $inspect) {}

    public function record(
        SagaInstance $sagaInstance,
        string $stepName,
        array $result,
    ): void {
        $sagaInstance = $sagaInstance->advanceTo(
            stepName : $stepName,
            stepIndex: $sagaInstance->currentStepIndex + 1,
            result   : $result,
        );

        $this->store->set('saga_' . $sagaInstance->id, $sagaInstance->toArray());

        $this->inspect->record(
            SagaRuntimeEvent::stepCompleted(
                sagaId  : $sagaInstance->id,
                sagaName: $sagaInstance->definitionName,
                stepName: $stepName,
                output  : $result['output'] ?? [],
            ),
        );
    }
}

final readonly class RecordSagaStepFailed
{
    public function __construct(private object $store, private object $inspect) {}

    public function record(
        SagaInstance $sagaInstance,
        string $stepName,
        string $error,
    ): void {
        $sagaInstance = $sagaInstance->fail(error: $error);

        $this->store->set('saga_' . $sagaInstance->id, $sagaInstance->toArray());

        $this->inspect->record(
            SagaRuntimeEvent::stepFailed(
                sagaId  : $sagaInstance->id,
                sagaName: $sagaInstance->definitionName,
                stepName: $stepName,
                error   : $error,
            ),
        );
    }
}

final readonly class ScheduleNextSagaStep
{
    public function schedule(
        SagaInstance $sagaInstance,
    ): SagaInstance {
        return $sagaInstance;
    }
}

final readonly class ChooseNextSagaStep
{
    public function choose(
        SagaInstance $sagaInstance,
        array $definition,
    ): ?SagaStepDefinition {
        $currentIndex = $sagaInstance->currentStepIndex;
        $nextIndex = $currentIndex + 1;

        if ($nextIndex >= count($definition['stepOrder'] ?? [])) {
            return null;
        }

        $nextStepName = $definition['stepOrder'][$nextIndex] ?? null;
        if ($nextStepName === null) {
            return null;
        }

        return $definition['steps'][$nextStepName] ?? null;
    }
}
