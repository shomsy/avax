<?php

declare(strict_types=1);

namespace Avax\DataModeling\Foundation\Exceptions;

/**
 * Thrown when dot notation path is invalid or unreachable.
 */
class InvalidCollectionPathException extends DataModelingException
{
    public static function pathNotFound(string $path) : self
    {
        return new self(message: "Path '{$path}' not found in collection.");
    }

    public static function invalidPathFormat(string $path) : self
    {
        return new self(message: "Invalid path format: '{$path}'.");
    }
}