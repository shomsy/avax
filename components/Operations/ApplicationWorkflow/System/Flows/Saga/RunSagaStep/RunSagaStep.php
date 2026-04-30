<?php

declare(strict_types=1);

namespace Avax\Components\Operations\ApplicationWorkflow\System\Flows\Saga\RunSagaStep;

use Avax\Components\Operations\ApplicationWorkflow\System\Flows\Saga\DefineSaga\SagaDefinition;
use Avax\Components\Operations\ApplicationWorkflow\System\Flows\Saga\DefineSaga\SagaStepDefinition;
use Avax\Components\Operations\ApplicationWorkflow\System\Flows\Saga\InspectSaga\InspectSaga;
use Avax\Components\Operations\ApplicationWorkflow\System\Flows\Saga\StartSaga\SagaInstance;
use Avax\Components\Operations\ApplicationWorkflow\System\Flows\Saga\StoreSagaState\StoreSagaState;
use Throwable;

final readonly class RunSagaStep
{
    public function __construct(
        private StoreSagaState $storeSagaState,
        private InspectSaga $inspectSaga,
    ) {}

    public function execute(
        SagaInstance $instance,
        SagaDefinition $definition,
        callable     $stepRunner,
    ) : SagaStepResult
    {
        $currentStep = $instance->currentStepName;
        if ($currentStep === null) {
            return SagaStepResult::failure(
                stepName: 'no_step',
                error   : 'No current step to execute.',
            );
        }

        $stepDef = $definition->getStep(name: $currentStep);
        if ($stepDef === null) {
            return SagaStepResult::failure(
                stepName: $currentStep,
                error   : sprintf('Step %s not found in definition.', $currentStep),
            );
        }

        $policy    = SagaStepExecutionPolicy::fromStep(step: $stepDef);
        $attempt   = 1;
        $startTime = microtime(true);

        while ( $attempt <= $policy->maxRetries + 1 ) {
            try {
                $output   = $stepRunner($stepDef, $instance->data);
                $duration = (microtime(true) - $startTime) * 1000;

                return SagaStepResult::success(
                    stepName  : $currentStep,
                    output    : is_array($output) ? $output : ['result' => $output],
                    attempt   : $attempt,
                    durationMs: $duration,
                );
            } catch (Throwable $e) {
                if (! $policy->canRetry(currentAttempt: $attempt)) {
                    $duration = (microtime(true) - $startTime) * 1000;

                    return SagaStepResult::failure(
                        stepName  : $currentStep,
                        error     : $e->getMessage(),
                        attempt   : $attempt,
                        durationMs: $duration,
                    );
                }

                $attempt++;
                usleep($policy->retryDelayMs * 1000);
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
        SagaInstance   $currentInstance,
        SagaStepResult $result,
        SagaDefinition $definition,
    ) : SagaInstance
    {
        if (! $result->success) {
            return $currentInstance->fail(error: $result->error ?? 'Unknown error');
        }

        $nextStep = $this->chooseNext(instance: $currentInstance, definition: $definition);
        if ($nextStep === null) {
            return $currentInstance->complete();
        }

        return $currentInstance->advanceTo(
            stepName : $nextStep->name,
            stepIndex: $currentInstance->currentStepIndex + 1,
            result   : $result->toArray(),
        );
    }

    public function chooseNext(SagaInstance $instance, SagaDefinition $definition) : SagaStepDefinition|null
    {
        $currentIndex = $instance->currentStepIndex;
        $nextIndex    = $currentIndex + 1;

        if ($nextIndex >= $definition->stepCount()) {
            return null;
        }

        $nextStepName = $definition->stepOrder[$nextIndex] ?? null;

        return $nextStepName ? $definition->getStep(name: $nextStepName) : null;
    }
}
