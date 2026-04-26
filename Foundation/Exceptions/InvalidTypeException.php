<?php

declare(strict_types=1);

namespace Avax\Exceptions;

use InvalidArgumentException;
use Override;

/**
 * Class InvalidTypeException
 *
 * Thrown when a value does not match the expected type.
 *
 * @deprecated Move to Foundation/DataFoundation/Exceptions/
 */
class InvalidTypeException extends InvalidArgumentException
{
    /**
     * InvalidTypeException constructor.
     *
     * @param string $expectedType The expected type.
     * @param mixed  $actualValue  The actual value that caused the exception.
     */
    #[Override]
    public function __construct(string $expectedType, mixed $actualValue)
    {
        $actualType = gettype(value: $actualValue);
        $message    = sprintf("Expected type '%s', but got '%s'.", $expectedType, $actualType);
        parent::__construct(message: $message);
    }
}
