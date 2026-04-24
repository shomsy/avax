<?php

declare(strict_types=1);

namespace Avax\ApplicationWorkflow\Saga\RunSagaStep;

use Avax\ApplicationWorkflow\Saga\StartSaga\SagaInstance;
use Avax\ApplicationWorkflow\Saga\DefineSaga\SagaDefinition;
use Avax\ApplicationWorkflow\Saga\DefineSaga\SagaStepDefinition;
use Avax\ApplicationWorkflow\Saga\StoreSagaState\StoreSagaState;
use Avax\ApplicationWorkflow\Saga\InspectSaga\InspectSaga;

final readonly class RunSagaStep
{
    public function __construct(
        private StoreSagaState $storeSagaState,
        private InspectSaga    $inspectSaga
    ) {}

    public function execute(
        SagaInstance   $instance,
        SagaDefinition $definition,
        callable       $stepRunner
    ) : SagaStepResult
    {
        $currentStep = $instance->currentStepName;
        if ($currentStep === null) {
            return SagaStepResult::failure(
                'no_step',
                'No current step to execute.'
            );
        }

        $stepDef = $definition->getStep($currentStep);
        if ($stepDef === null) {
            return SagaStepResult::failure(
                $currentStep,
                sprintf('Step %s not found in definition.', $currentStep)
            );
        }

        $policy    = SagaStepExecutionPolicy::fromStep($stepDef);
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
                    durationMs: $duration
                );
            } catch (\Throwable $e) {
                if (! $policy->canRetry($attempt)) {
                    $duration = (microtime(true) - $startTime) * 1000;

                    return SagaStepResult::failure(
                        stepName  : $currentStep,
                        error     : $e->getMessage(),
                        attempt   : $attempt,
                        durationMs: $duration
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
            durationMs: $duration
        );
    }

    public function chooseNext(SagaInstance $instance, SagaDefinition $definition) : ?SagaStepDefinition
    {
        $currentIndex = $instance->currentStepIndex;
        $nextIndex    = $currentIndex + 1;

        if ($nextIndex >= $definition->stepCount()) {
            return null;
        }

        $nextStepName = $definition->stepOrder[$nextIndex] ?? null;

        return $nextStepName ? $definition->getStep($nextStepName) : null;
    }

    public function scheduleNext(
        SagaInstance   $currentInstance,
        SagaStepResult $result,
        SagaDefinition $definition
    ) : SagaInstance
    {
        if (! $result->success) {
            return $currentInstance->fail($result->error ?? 'Unknown error');
        }

        $nextStep = $this->chooseNext($currentInstance, $definition);
        if ($nextStep === null) {
            return $currentInstance->complete();
        }

        return $currentInstance->advanceTo(
            stepName : $nextStep->name,
            stepIndex: $currentInstance->currentStepIndex + 1,
            result   : $result->toArray()
        );
    }
}