<?php

declare(strict_types=1);

namespace Avax\DataFoundation\Exceptions;

/**
 * Raised when a semantic value object receives an invalid primitive value.
 */
final class InvalidValueException extends DataFoundationException
{
    public static function because(string $message) : self
    {
        return new self(message: $message);
    }
}
