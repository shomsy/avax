<?php

declare(strict_types=1);

namespace Avax\Components\DataStack\Data\System\Foundation\Exceptions;

/**
 * Raised when a flow abstraction is configured with invalid semantics.
 */
final class InvalidFlowException extends DataException
{
    public static function invalidWindowSize(int $size): self
    {
        return new self(message: "Window size must be greater than zero, got '{$size}'.");
    }

    public static function invalidBatchSize(int $size): self
    {
        return new self(message: "Batch size must be greater than zero, got '{$size}'.");
    }

    public static function pipelineHasNoStages(): self
    {
        return new self(message: 'Pipeline has no stages to execute.');
    }
}
