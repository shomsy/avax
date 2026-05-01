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
        SagaStepDefinition $step,
        array $sagaData,
    ): array {
        $startTime = microtime(true);

        $input = $this->prepareInput(stepInput: $step->input, sagaData: $sagaData);

        $result = ($this->stepRunner)($step->component, $input);

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
        $data = $this->store->get("saga_{$sagaId}");
        if ($data === null) {
            return null;
        }

        return SagaInstance::fromArray(row: $data);
    }

    public function exists(string $sagaId): bool
    {
        return $this->store->get("saga_{$sagaId}") !== null;
    }
}

final readonly class RecordSagaStepCompleted
{
    public function __construct(private object $store, private object $inspect) {}

    public function record(
        SagaInstance $instance,
        string $stepName,
        array $result,
    ): void {
        $instance = $instance->advanceTo(
            stepName : $stepName,
            stepIndex: $instance->currentStepIndex + 1,
            result   : $result,
        );

        $this->store->set("saga_{$instance->id}", $instance->toArray());

        $this->inspect->record(
            SagaRuntimeEvent::stepCompleted(
                sagaId  : $instance->id,
                sagaName: $instance->definitionName,
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
        SagaInstance $instance,
        string $stepName,
        string $error,
    ): void {
        $instance = $instance->fail(error: $error);

        $this->store->set("saga_{$instance->id}", $instance->toArray());

        $this->inspect->record(
            SagaRuntimeEvent::stepFailed(
                sagaId  : $instance->id,
                sagaName: $instance->definitionName,
                stepName: $stepName,
                error   : $error,
            ),
        );
    }
}

final readonly class ScheduleNextSagaStep
{
    public function schedule(
        SagaInstance $current,
        SagaStepDefinition $nextStep,
    ): SagaInstance {
        return $current;
    }
}

final readonly class ChooseNextSagaStep
{
    public function choose(
        SagaInstance $instance,
        array $definition,
    ): ?SagaStepDefinition {
        $currentIndex = $instance->currentStepIndex;
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
