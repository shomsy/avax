<?php

declare(strict_types=1);

namespace Avax\Components\Operations\ApplicationWorkflow\System\Flows\Saga\RunSagaStep;

use Avax\Components\Operations\ApplicationWorkflow\System\Flows\Saga\DefineSaga\SagaDefinition;
use Avax\Components\Operations\ApplicationWorkflow\System\Flows\Saga\DefineSaga\SagaStepDefinition;
use Avax\Components\Operations\ApplicationWorkflow\System\Flows\Saga\StartSaga\SagaInstance;
use Avax\Components\Operations\Resilience\System\PublicSurface\Resilience;

final readonly class RunSagaStep
{
    public function execute(
        SagaInstance   $sagaInstance,
        SagaDefinition $sagaDefinition,
        callable       $stepRunner,
    ) : SagaStepResult
    {
        $currentStep = $sagaInstance->currentStepName;
        if ($currentStep === null) {
            return SagaStepResult::failure(
                stepName: 'no_step',
                error   : 'No current step to execute.',
            );
        }

        $stepDef = $sagaDefinition->getStep(name: $currentStep);
        if (! $stepDef instanceof SagaStepDefinition) {
            return SagaStepResult::failure(
                stepName: $currentStep,
                error   : sprintf('Step %s not found in definition.', $currentStep),
            );
        }

        $sagaStepExecutionPolicy = SagaStepExecutionPolicy::fromStep(step: $stepDef);
        $maxAttempts = $sagaStepExecutionPolicy->maxRetries + 1;
        $startTime               = microtime(true);

        $result = Resilience::retry(function () use ($stepRunner, $stepDef, $sagaInstance) : mixed {
            return $stepRunner($stepDef, $sagaInstance->data);
        })
            ->times($maxAttempts)
            ->backoff($sagaStepExecutionPolicy->retryDelayMs)
            ->run();

        $duration = (microtime(true) - $startTime) * 1000;

        if ($result->success) {
            return SagaStepResult::success(
                stepName  : $currentStep,
                output    : is_array($result->result) ? $result->result : ['result' => $result->result],
                attempt   : $result->attempts,
                durationMs: $duration,
            );
        }

        return SagaStepResult::failure(
            stepName  : $currentStep,
            error     : $result->lastException?->getMessage() ?? '_MAX_RETRIES_EXCEEDED',
            attempt   : $result->attempts,
            durationMs: $duration,
        );
    }

    public function scheduleNext(
        SagaInstance   $sagaInstance,
        SagaStepResult $sagaStepResult,
        SagaDefinition $sagaDefinition,
    ) : SagaInstance
    {
        if (! $sagaStepResult->success) {
            return $sagaInstance->fail(error: $sagaStepResult->error ?? 'Unknown error');
        }

        $nextStep = $this->chooseNext(instance: $sagaInstance, definition: $sagaDefinition);
        if (! $nextStep instanceof SagaStepDefinition) {
            return $sagaInstance->complete();
        }

        return $sagaInstance->advanceTo(
            stepName : $nextStep->name,
            stepIndex: $sagaInstance->currentStepIndex + 1,
            result   : $sagaStepResult->toArray(),
        );
    }

    public function chooseNext(SagaInstance $sagaInstance, SagaDefinition $sagaDefinition) : ?SagaStepDefinition
    {
        $currentIndex = $sagaInstance->currentStepIndex;
        $nextIndex    = $currentIndex + 1;

        if ($nextIndex >= $sagaDefinition->stepCount()) {
            return null;
        }

        $nextStepName = $sagaDefinition->stepOrder[$nextIndex] ?? null;

        return $nextStepName ? $sagaDefinition->getStep(name: $nextStepName) : null;
    }
}
