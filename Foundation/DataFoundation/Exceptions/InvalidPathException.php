<?php

declare(strict_types=1);

namespace Avax\DataFoundation\Exceptions;

/**
 * Raised when a path cannot be parsed or resolved.
 */
class InvalidPathException extends DataFoundationException
{
    public static function pathNotFound(string $path) : self
    {
        return new self(message: "Path '{$path}' was not found.");
    }

    public static function invalidPathFormat(string $path) : self
    {
        return new self(message: "Invalid path format: '{$path}'.");
    }
}
