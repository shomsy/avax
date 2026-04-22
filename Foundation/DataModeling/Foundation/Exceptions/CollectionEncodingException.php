<?php

declare(strict_types=1);

namespace Avax\DataModeling\Foundation\Exceptions;

/**
 * Thrown when JSON or XML encoding fails.
 */
class CollectionEncodingException extends DataModelingException
{
    public static function jsonEncodingFailed(string $error) : self
    {
        return new self(message: "JSON encoding failed: {$error}");
    }

    public static function xmlEncodingFailed(string $error) : self
    {
        return new self(message: "XML encoding failed: {$error}");
    }
}