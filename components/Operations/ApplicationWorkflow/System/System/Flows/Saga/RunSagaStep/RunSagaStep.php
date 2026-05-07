<?php

declare(strict_types=1);

namespace Avax\Components\Operations\ApplicationWorkflow\System\System\Flows\Saga\RunSagaStep;

use Avax\Components\Operations\ApplicationWorkflow\System\System\Flows\Saga\DefineSaga\SagaDefinition;
use Avax\Components\Operations\ApplicationWorkflow\System\System\Flows\Saga\DefineSaga\SagaStepDefinition;
use Avax\Components\Operations\ApplicationWorkflow\System\System\Flows\Saga\StartSaga\SagaInstance;
use Throwable;

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
        $attempt                 = 1;
        $startTime               = microtime(true);

        while ( $attempt <= $sagaStepExecutionPolicy->maxRetries + 1 ) {
            try {
                $output   = $stepRunner($stepDef, $sagaInstance->data);
                $duration = (microtime(true) - $startTime) * 1000;

                return SagaStepResult::success(
                    stepName  : $currentStep,
                    output    : is_array($output) ? $output : ['result' => $output],
                    attempt   : $attempt,
                    durationMs: $duration,
                );
            } catch (Throwable $e) {
                if (! $sagaStepExecutionPolicy->canRetry(currentAttempt: $attempt)) {
                    $duration = (microtime(true) - $startTime) * 1000;

                    return SagaStepResult::failure(
                        stepName  : $currentStep,
                        error     : $e->getMessage(),
                        attempt   : $attempt,
                        durationMs: $duration,
                    );
                }

                $attempt++;
                usleep($sagaStepExecutionPolicy->retryDelayMs * 1000);
            }
        }

        $duration = (microtime(true) - $startTime) * 1000;

        return SagaStepResult::failure(
            stepName  : $currentStep,
            error     : '_MAX_RETRIES_EXCEEDED',
            attempt   : $attempt,
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
