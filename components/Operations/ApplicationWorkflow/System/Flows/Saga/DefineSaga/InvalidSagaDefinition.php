<?php

declare(strict_types=1);

namespace Avax\Components\Operations\ApplicationWorkflow\System\Flows\Saga\DefineSaga;

use RuntimeException;

/**
 * InvalidSagaDefinition - reports a workflow definition that cannot safely reach runtime.
 */
final class InvalidSagaDefinition extends RuntimeException
{
    public static function missingName(): self
    {
        return new self(message: 'Saga definition requires a non-empty name.');
    }

    public static function missingFirstStep(string $firstStepName): self
    {
        return new self(message: sprintf('Saga definition points to unknown first step %s.', $firstStepName));
    }

    public static function duplicateStep(string $stepName): self
    {
        return new self(message: sprintf('Saga definition contains duplicate step %s.', $stepName));
    }

    public static function unknownNextStep(string $stepName, string $nextStepName): self
    {
        return new self(message: sprintf('Saga step %s points to unknown next step %s.', $stepName, $nextStepName));
    }

    public static function compensationWithoutSideEffect(string $stepName): self
    {
        return new self(message: sprintf('Saga step %s defines compensation but is not marked as a side-effect step.', $stepName));
    }
}
