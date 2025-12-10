<?php

declare(strict_types=1);

namespace Avax\HTTP\Session\Data;

use RuntimeException;
use Throwable;

/**
 * Session Component
 *
 * Serializer - Session Data Serialization
 *
 * Handles serialization and deserialization of session data.
 * Supports multiple formats for different use cases.
 *
 * Supported formats:
 * - PHP native serialize/unserialize
 * - JSON encoding
 * - Base64 encoding for safe transport
 *
 * @author  Milos Stankovic
 * @package Shomsy\Components\Foundation\HTTP\Session\Data
 */
final class Serializer
{
    /**
     * Serialize data to JSON.
     *
     * @param mixed $data Data to serialize.
     *
     * @return string JSON string.
     *
     * @throws RuntimeException If JSON encoding fails.
     * @throws \JsonException
     */
    public function toJson(mixed $data) : string
    {
        $json = json_encode(value: $data, flags: JSON_THROW_ON_ERROR);

        if ($json === false) {
            throw new RuntimeException(message: 'Failed to encode data to JSON');
        }

        return $json;
    }

    /**
     * Deserialize data from JSON.
     *
     * @param string $json JSON string.
     *
     * @return mixed Decoded data.
     *
     * @throws RuntimeException If JSON decoding fails.
     */
    public function fromJson(string $json) : mixed
    {
        try {
            return json_decode(json: $json, associative: true, flags: JSON_THROW_ON_ERROR);
        } catch (Throwable $e) {
            throw new RuntimeException(message: 'Failed to decode JSON: ' . $e->getMessage(), previous: $e);
        }
    }

    /**
     * Encode data to base64 for safe transport.
     *
     * @param mixed $data Data to encode.
     *
     * @return string Base64 encoded string.
     */
    public function toBase64(mixed $data) : string
    {
        return base64_encode(string: $this->serialize(data: $data));
    }

    /**
     * Serialize data using PHP's native serialization.
     *
     * @param mixed $data Data to serialize.
     *
     * @return string Serialized data.
     */
    public function serialize(mixed $data) : string
    {
        return serialize(value: $data);
    }

    /**
     * Decode data from base64.
     *
     * @param string $encoded Base64 encoded string.
     *
     * @return mixed Decoded data.
     *
     * @throws RuntimeException If decoding fails.
     */
    public function fromBase64(string $encoded) : mixed
    {
        $decoded = base64_decode(string: $encoded, strict: true);

        if ($decoded === false) {
            throw new RuntimeException(message: 'Failed to decode base64 data');
        }

        return $this->unserialize(data: $decoded);
    }

    /**
     * Unserialize data using PHP's native unserialization.
     *
     * @param string $data Serialized data.
     *
     * @return mixed Unserialized data.
     *
     * @throws RuntimeException If unserialization fails.
     */
    public function unserialize(string $data) : mixed
    {
        try {
            return unserialize(data: $data);
        } catch (Throwable $e) {
            throw new RuntimeException(message: 'Failed to unserialize data: ' . $e->getMessage(), previous: $e);
        }
    }

    /**
     * Check if data is serialized.
     *
     * @param string $data Data to check.
     *
     * @return bool True if data appears to be serialized.
     */
    public function isSerialized(string $data) : bool
    {
        $data = trim(string: $data);

        if ($data === 'N;') {
            return true;
        }

        if (strlen(string: $data) < 4) {
            return false;
        }

        if ($data[1] !== ':') {
            return false;
        }

        $lastChar = $data[strlen(string: $data) - 1];
        if ($lastChar !== ';' && $lastChar !== '}') {
            return false;
        }

        $token = $data[0];
        switch ($token) {
            case 's':
            case 'a':
            case 'O':
                return (bool) preg_match(pattern: "/^{$token}:[0-9]+:/s", subject: $data);
            case 'b':
            case 'i':
            case 'd':
                return (bool) preg_match(pattern: "/^{$token}:[0-9.E+-]+;$/", subject: $data);
        }

        return false;
    }

    /**
     * Safely unserialize data with validation.
     *
     * @param string $data           Serialized data.
     * @param array  $allowedClasses Allowed classes for unserialization.
     *
     * @return mixed Unserialized data.
     *
     * @throws RuntimeException If unserialization fails or contains disallowed classes.
     */
    public function safeUnserialize(string $data, array $allowedClasses = []) : mixed
    {
        try {
            return unserialize(data: $data, options: ['allowed_classes' => $allowedClasses]);
        } catch (Throwable $e) {
            throw new RuntimeException(message: 'Failed to safely unserialize data: ' . $e->getMessage(), previous: $e);
        }
    }
}
