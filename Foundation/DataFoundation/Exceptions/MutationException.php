<?php

declare(strict_types=1);

namespace Avax\DataFoundation\Exceptions;

/**
 * Raised when an immutable value is mutated.
 */
class MutationException extends DataFoundationException
{
    public static function valueIsLocked() : self
    {
        return new self(message: 'Value is locked and cannot be modified.');
    }

    public static function valueIsAlreadyLocked() : self
    {
        return new self(message: 'Value is already locked.');
    }

    public static function collectionIsLocked() : self
    {
        return new self(message: 'Collection is locked and cannot be modified.');
    }

    public static function collectionIsAlreadyLocked() : self
    {
        return new self(message: 'Collection is already locked.');
    }
}
