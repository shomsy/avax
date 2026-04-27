<?php

declare(strict_types=1);

namespace Avax\DataFoundation\Exceptions;

/**
 * Raised when data encoding or decoding fails.
 */
class EncodingException extends DataFoundationException
{
    public static function jsonEncodingFailed(string $error) : self
    {
        return new self(message: "JSON encoding failed: {$error}");
    }

    public static function jsonDecodingFailed(string $error) : self
    {
        return new self(message: "JSON decoding failed: {$error}");
    }

    public static function xmlEncodingFailed(string $error) : self
    {
        return new self(message: "XML encoding failed: {$error}");
    }

    public static function xmlDecodingFailed(string $error) : self
    {
        return new self(message: "XML decoding failed: {$error}");
    }
}
