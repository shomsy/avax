<?php

declare(strict_types=1);

namespace Avax\Components\DataStack\Data\System\Capabilities\Collections\Exceptions;

use RuntimeException;

/**
 * Raised when an immutable collection or value is mutated.
 */
final class MutationException extends RuntimeException
{
    public static function valueIsLocked(): self
    {
        return new self(message: 'Value is locked and cannot be modified.');
    }

    public static function valueIsAlreadyLocked(): self
    {
        return new self(message: 'Value is already locked.');
    }

    public static function collectionIsLocked(): self
    {
        return new self(message: 'Collection is locked and cannot be modified.');
    }

    public static function collectionIsAlreadyLocked(): self
    {
        return new self(message: 'Collection is already locked.');
    }

    /**
     * Thrown when array-style mutation is attempted on an immutable collection.
     *
     * @param string $hint Guidance on which fluent method to use instead.
     */
    public static function arrayStyleMutationNotSupported(string $hint = '') : self
    {
        $message = 'Array-style mutation is not supported on immutable collections.';

        if ($hint !== '') {
            $message .= ' ' . $hint;
        }

        return new self(message: $message);
    }
}
