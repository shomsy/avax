<?php

declare(strict_types=1);

namespace Avax\Framework\Foundation\Exception;

use RuntimeException;
use Throwable;

/**
 * Thrown when a feature or method is not yet implemented.
 *
 * Use this instead of empty stubs or fake returns to make
 * the incomplete state explicit and discoverable at runtime.
 */
final class NotImplementedException extends RuntimeException
{
    public function __construct(string $message = 'Not implemented', int $code = 0, ?Throwable $previous = null)
    {
        parent::__construct($message, $code, $previous);
    }
}
