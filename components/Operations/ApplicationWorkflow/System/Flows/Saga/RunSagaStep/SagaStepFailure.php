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
    public static function unknownStep(string $stepName) : self
    {
        return new self(message: sprintf('Saga step %s does not exist in the definition.', $stepName));
    }

    public static function failed(string $stepName, Throwable $throwable) : self
    {
        return new self(message: sprintf('Saga step %s failed.', $stepName), previous: $throwable);
    }
}
