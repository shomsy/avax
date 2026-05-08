<?php

declare(strict_types=1);

namespace Avax\Components\DataStack\Data\System\Foundation\Failure;

use RuntimeException;
use Throwable;

/**
 * Raised when JSON parsing or encoding fails.
 */
final class InvalidJson extends RuntimeException
{
    /**
     * @param Throwable|null $previous The underlying JsonException, if any.
     */
    public static function malformed(Throwable|null $previous = null) : self
    {
        return new self(
            message : 'The provided string is not valid JSON.',
            previous: $previous,
        );
    }
}
