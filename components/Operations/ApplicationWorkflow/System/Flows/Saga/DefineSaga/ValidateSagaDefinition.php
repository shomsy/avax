<?php

declare(strict_types=1);

namespace Avax\Components\Operations\ApplicationWorkflow\System\Flows\Saga\DefineSaga;

final readonly class ValidateSagaDefinition
{
    public function validate(SagaDefinition $sagaDefinition) : SagaValidationResult
    {
        $errors   = [];
        $warnings = [];

        if ($sagaDefinition->stepCount() === 0) {
            $errors[] = 'Saga must have at least one step.';
        }

        if ($sagaDefinition->stepCount() > 1) {
            $hasCompensation = false;
            foreach ($sagaDefinition as $step) {
                if ($step->hasCompensation()) {
                    $hasCompensation = true;

                    break;
                }
            }

            if (! $hasCompensation) {
                $warnings[] = 'Multi-step saga has no compensation steps. This may lead to orphaned data on failure.';
            }
        }

        $stepNames = [];
        foreach ($sagaDefinition as $step) {
            if (in_array($step->name, $stepNames, true)) {
                $errors[] = sprintf('Duplicate step name: %s', $step->name);
            }

            $stepNames[] = $step->name;
        }

        foreach ($sagaDefinition as $step) {
            if ($step->timeoutSeconds <= 0) {
                $errors[] = sprintf('Step %s has invalid timeout: %d', $step->name, $step->timeoutSeconds);
            }

            if ($step->maxRetries < 0) {
                $errors[] = sprintf('Step %s has invalid max retries: %d', $step->name, $step->maxRetries);
            }

            if ($step->maxRetries > 0 && $step->retryPolicy === SagaStepRetryPolicy::NONE) {
                $warnings[] = sprintf('Step %s has retries but no retry policy.', $step->name);
            }

            if ($step->hasCompensation() && empty($step->compensationComponent)) {
                $errors[] = sprintf('Step %s declares compensation but has no component.', $step->name);
            }
        }

        $totalTimeout = $sagaDefinition->totalTimeout();
        if ($totalTimeout > $sagaDefinition->maxDurationSeconds) {
            $warnings[] = sprintf(
                'Step timeouts (%ds) exceed max saga duration (%ds).',
                $totalTimeout,
                $sagaDefinition->maxDurationSeconds,
            );
        }

        if ($sagaDefinition->status === SagaStatus::INVALID) {
            $errors[] = 'Saga definition is marked as invalid.';
        }

        return new SagaValidationResult(errors: $errors, warnings: $warnings);
    }
}
