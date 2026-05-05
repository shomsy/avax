<?php

declare(strict_types=1);

namespace Avax\Components\DataStack\Data\System\Foundation\Exceptions;

/**
 * Raised when a semantic value object receives an invalid primitive value.
 */
final class InvalidValueException extends DataException
{
    public static function because(string $message) : self
    {
        return new self(message: $message);
    }
}
