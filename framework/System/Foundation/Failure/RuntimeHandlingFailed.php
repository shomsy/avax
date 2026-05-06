<?php

declare(strict_types=1);

namespace Avax\Framework\System\Foundation\Failure;

use Throwable;

/**
 * Exception thrown when runtime error handling fails.
 */
class RuntimeHandlingFailed extends FrameworkFailure
{
    public static function fromThrowable(Throwable $throwable): self
    {
        return new self(
            message : sprintf('Runtime error handling failed: %s', $throwable->getMessage()),
            code    : (int) $throwable->getCode(),
            previous: $throwable,
        );
    }
}
