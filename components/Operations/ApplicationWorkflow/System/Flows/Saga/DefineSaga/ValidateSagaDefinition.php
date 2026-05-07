<?php

declare(strict_types=1);

namespace Avax\Components\Operations\ApplicationWorkflow\System\Flows\Saga\DefineSaga;

final readonly class ValidateSagaDefinition
{
    public array $errors;

    public array $warnings;

    public function __construct(private SagaDefinition $sagaDefinition)
    {
        $this->errors   = [];
        $this->warnings = [];
        $this->validate();
    }

    private function validate() : void
    {
        if ($this->sagaDefinition->stepCount() === 0) {
            $this->errors[] = 'Saga must have at least one step.';
        }

        if ($this->sagaDefinition->stepCount() > 1) {
            $hasCompensation = false;
            foreach ($this->sagaDefinition as $step) {
                if ($step->hasCompensation()) {
                    $hasCompensation = true;

                    break;
                }
            }

            if (! $hasCompensation) {
                $this->warnings[] = 'Multi-step saga has no compensation steps. This may lead to orphaned data on failure.';
            }
        }

        $stepNames = [];
        foreach ($this->sagaDefinition as $step) {
            if (in_array($step->name, $stepNames, true)) {
                $this->errors[] = sprintf('Duplicate step name: %s', $step->name);
            }

            $stepNames[] = $step->name;
        }

        foreach ($this->sagaDefinition as $step) {
            if ($step->timeoutSeconds <= 0) {
                $this->errors[] = sprintf('Step %s has invalid timeout: %d', $step->name, $step->timeoutSeconds);
            }

            if ($step->maxRetries < 0) {
                $this->errors[] = sprintf('Step %s has invalid max retries: %d', $step->name, $step->maxRetries);
            }

            if ($step->maxRetries > 0 && $step->retryPolicy === SagaStepRetryPolicy::NONE) {
                $this->warnings[] = sprintf('Step %s has retries but no retry policy.', $step->name);
            }

            if ($step->hasCompensation() && empty($step->compensationComponent)) {
                $this->errors[] = sprintf('Step %s declares compensation but has no component.', $step->name);
            }
        }

        $totalTimeout = $this->sagaDefinition->totalTimeout();
        if ($totalTimeout > $this->sagaDefinition->maxDurationSeconds) {
            $this->warnings[] = sprintf(
                'Step timeouts (%ds) exceed max saga duration (%ds).',
                $totalTimeout,
                $this->sagaDefinition->maxDurationSeconds,
            );
        }

        if ($this->sagaDefinition->status === SagaStatus::INVALID) {
            $this->errors[] = 'Saga definition is marked as invalid.';
        }
    }

    public function isValid() : bool
    {
        return $this->errors === [];
    }

    public function getErrors() : array
    {
        return $this->errors;
    }

    public function getWarnings() : array
    {
        return $this->warnings;
    }
}
