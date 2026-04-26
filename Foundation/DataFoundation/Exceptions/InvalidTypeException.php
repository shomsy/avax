<?php

declare(strict_types=1);

namespace Avax\DataFoundation\Exceptions;

use InvalidArgumentException;

/**
 * Thrown when a value does not match the expected type during data transfer.
 */
class InvalidTypeException extends InvalidArgumentException
{
    /**
     * InvalidTypeException constructor.
     *
     * @param string $expectedType The expected type.
     * @param mixed  $actualValue  The actual value that caused the exception.
     */
    public function __construct(string $expectedType, mixed $actualValue)
    {
        $actualType = gettype(value: $actualValue);
        $message    = sprintf("Expected type '%s', but got '%s'.", $expectedType, $actualType);
        parent::__construct(message: $message);
    }
}
