<?php

declare(strict_types=1);

namespace Avax\DataModeling\Foundation\Exceptions;

/**
 * Thrown when mutation is attempted on locked collection.
 */
class CollectionMutationException extends DataModelingException
{
    public static function collectionIsLocked() : self
    {
        return new self(message: 'Collection is locked and cannot be modified.');
    }

    public static function collectionIsAlreadyLocked() : self
    {
        return new self(message: 'Collection is already locked.');
    }
}