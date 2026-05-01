<?php

declare(strict_types=1);

namespace Avax\Components\Operations\ApplicationWorkflow\System\Flows\Saga\RunSagaStep;

use RuntimeException;
use Throwable;

/**
 * SagaStepFailure - reports a failed saga step after the failure event has been recorded.
 */
final class SagaStepFailure extends RuntimeException
{
    public static function unknownStep(string $stepName): self
    {
        return new self(message: "Saga step {$stepName} does not exist in the definition.");
    }

    public static function failed(string $stepName, Throwable $previous): self
    {
        return new self(message: "Saga step {$stepName} failed.", previous: $previous);
    }
}
