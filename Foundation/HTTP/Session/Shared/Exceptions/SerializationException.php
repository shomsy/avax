<?php

declare(strict_types=1);

namespace Avax\HTTP\Session\Shared\Exceptions;

use RuntimeException;
use Throwable;

/**
 * SerializationException
 *
 * Thrown when serialization or deserialization operations fail.
 *
 * @package Avax\HTTP\Session\Shared\Exceptions
 */
final class SerializationException extends RuntimeException
{
    /**
     * Create exception for serialization failure.
     *
     * @param string         $reason   Reason for failure.
     * @param Throwable|null $previous Previous exception.
     *
     * @return self
     */
    public static function serializationFailed(string $reason, ?Throwable $previous = null) : self
    {
        return new self(
            message : "Serialization failed: {$reason}",
            code    : 0,
            previous: $previous
        );
    }

    /**
     * Create exception for deserialization failure.
     *
     * @param string         $reason   Reason for failure.
     * @param Throwable|null $previous Previous exception.
     *
     * @return self
     */
    public static function deserializationFailed(string $reason, ?Throwable $previous = null) : self
    {
        return new self(
            message : "Deserialization failed: {$reason}",
            code    : 0,
            previous: $previous
        );
    }

    /**
     * Create exception for compression failure.
     *
     * @return self
     */
    public static function compressionFailed() : self
    {
        return new self(message: 'Data compression failed');
    }

    /**
     * Create exception for decompression failure.
     *
     * @return self
     */
    public static function decompressionFailed() : self
    {
        return new self(message: 'Data decompression failed');
    }

    /**
     * Create exception for integrity check failure.
     *
     * @return self
     */
    public static function integrityCheckFailed() : self
    {
        return new self(message: 'Data integrity check failed - checksum mismatch');
    }

    /**
     * Create exception for invalid format.
     *
     * @param string $reason Reason for invalid format.
     *
     * @return self
     */
    public static function invalidFormat(string $reason) : self
    {
        return new self(message: "Invalid data format: {$reason}");
    }

    /**
     * Create exception for JSON encoding failure.
     *
     * @param string         $reason   Reason for failure.
     * @param Throwable|null $previous Previous exception.
     *
     * @return self
     */
    public static function jsonEncodeFailed(string $reason, ?Throwable $previous = null) : self
    {
        return new self(
            message : "JSON encoding failed: {$reason}",
            code    : 0,
            previous: $previous
        );
    }

    /**
     * Create exception for JSON decoding failure.
     *
     * @param string         $reason   Reason for failure.
     * @param Throwable|null $previous Previous exception.
     *
     * @return self
     */
    public static function jsonDecodeFailed(string $reason, ?Throwable $previous = null) : self
    {
        return new self(
            message : "JSON decoding failed: {$reason}",
            code    : 0,
            previous: $previous
        );
    }
}
