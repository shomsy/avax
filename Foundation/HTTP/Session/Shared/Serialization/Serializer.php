<?php

declare(strict_types=1);

namespace Avax\HTTP\Session\Shared\Serialization;

use Avax\HTTP\Session\Shared\Exceptions\SerializationException;
use JsonException;
use Throwable;

/**
 * Serializer - Enterprise-Grade Serialization Handler
 * ============================================================
 *
 * Provides secure, type-safe serialization and deserialization
 * for session data with built-in security measures.
 *
 * Enterprise Features:
 * - Safe deserialization (prevents object injection attacks)
 * - Type validation and sanitization
 * - Compression support for large payloads
 * - Integrity verification via checksums
 * - Detailed error handling and logging
 * - Support for custom serialization formats
 *
 * Security:
 * - Prevents arbitrary object instantiation during unserialize
 * - Validates data integrity before deserialization
 * - Sanitizes input to prevent injection attacks
 * - Implements whitelist-based class filtering
 *
 * @package Avax\HTTP\Session\Shared\Serialization
 * @author  Milos
 * @version 1.0
 */
final class Serializer
{
    /**
     * Allowed classes for safe deserialization.
     *
     * Only these classes can be instantiated during unserialize.
     * Empty array = no objects allowed (arrays and scalars only).
     *
     * @var array<int, string>
     */
    private array $allowedClasses = [];

    /**
     * Enable compression for large payloads.
     *
     * @var bool
     */
    private bool $compressionEnabled = false;

    /**
     * Compression threshold in bytes.
     *
     * Data larger than this will be compressed.
     *
     * @var int
     */
    private int $compressionThreshold = 1024;

    /**
     * Enable integrity checking via checksums.
     *
     * @var bool
     */
    private bool $integrityCheckEnabled = true;

    /**
     * Serializer Constructor.
     *
     * @param array<int, string> $allowedClasses       Classes allowed during deserialization.
     * @param bool               $compressionEnabled   Enable compression for large data.
     * @param int                $compressionThreshold Minimum size for compression (bytes).
     * @param bool               $integrityCheck       Enable checksum verification.
     */
    public function __construct(
        array $allowedClasses = [],
        bool  $compressionEnabled = false,
        int   $compressionThreshold = 1024,
        bool  $integrityCheck = true
    )
    {
        $this->allowedClasses        = $allowedClasses;
        $this->compressionEnabled    = $compressionEnabled;
        $this->compressionThreshold  = $compressionThreshold;
        $this->integrityCheckEnabled = $integrityCheck;
    }

    // ----------------------------------------------------------------
    // Core Serialization
    // ----------------------------------------------------------------

    /**
     * Deserialize data with security checks.
     *
     * This method implements safe deserialization by:
     * 1. Verifying data integrity (if enabled)
     * 2. Decompressing data (if compressed)
     * 3. Using allowed_classes to prevent object injection
     *
     * @param string $data The serialized data.
     *
     * @return mixed The deserialized data.
     *
     * @throws SerializationException If deserialization fails or integrity check fails.
     */
    public function deserialize(string $data) : mixed
    {
        try {
            // Step 1: Verify integrity checksum if enabled
            if ($this->integrityCheckEnabled) {
                if (! str_contains(haystack: $data, needle: ':')) {
                    throw SerializationException::invalidFormat(reason: 'Missing checksum separator');
                }

                [$checksum, $payload] = explode(separator: ':', string: $data, limit: 2);

                $expectedChecksum = hash(algo: 'sha256', data: $payload);
                if (! hash_equals(known_string: $expectedChecksum, user_string: $checksum)) {
                    throw SerializationException::integrityCheckFailed();
                }

                $data = $payload;
            }

            // Step 2: Decompress if data was compressed
            if (str_starts_with(haystack: $data, needle: 'GZIP:')) {
                $compressed   = substr(string: $data, offset: 5);
                $decompressed = gzuncompress(data: $compressed);
                if ($decompressed === false) {
                    throw SerializationException::decompressionFailed();
                }
                $data = $decompressed;
            }

            // Step 3: Safe unserialize with allowed_classes
            $result = unserialize(data: $data, options: ['allowed_classes' => $this->allowedClasses]);

            if ($result === false && $data !== serialize(value: false)) {
                throw SerializationException::deserializationFailed(reason: 'Unserialize returned false');
            }

            return $result;
        } catch (SerializationException $e) {
            throw $e;
        } catch (Throwable $e) {
            throw SerializationException::deserializationFailed(reason: $e->getMessage(), previous: $e);
        }
    }

    /**
     * Safe deserialization that NEVER allows object instantiation.
     *
     * This is the most secure option - only arrays and scalar values
     * can be deserialized. No objects are allowed.
     *
     * Use this for untrusted data or when you only need arrays/scalars.
     *
     * @param string $data The serialized data.
     *
     * @return mixed The deserialized data (arrays and scalars only).
     *
     * @throws SerializationException If deserialization fails.
     */
    public function safeUnserialize(string $data) : mixed
    {
        try {
            // Force allowed_classes to false (no objects allowed)
            $result = unserialize(data: $data, options: ['allowed_classes' => false]);

            if ($result === false && $data !== serialize(value: false)) {
                throw SerializationException::deserializationFailed(reason: 'Safe unserialize returned false');
            }

            return $result;
        } catch (Throwable $e) {
            throw SerializationException::deserializationFailed(reason: $e->getMessage(), previous: $e);
        }
    }

    // ----------------------------------------------------------------
    // Safe Deserialization (Legacy Compatibility)
    // ----------------------------------------------------------------

    /**
     * Serialize data to JSON format.
     *
     * JSON is safer than PHP serialize for untrusted data and
     * is also more portable across different systems.
     *
     * @param mixed $data  The data to serialize.
     * @param int   $flags JSON encoding flags.
     *
     * @return string JSON-encoded data.
     *
     * @throws SerializationException If JSON encoding fails.
     */
    public function toJson(mixed $data, int $flags = JSON_THROW_ON_ERROR) : string
    {
        try {
            return json_encode(value: $data, flags: $flags);
        } catch (JsonException $e) {
            throw SerializationException::jsonEncodeFailed(reason: $e->getMessage(), previous: $e);
        }
    }

    // ----------------------------------------------------------------
    // JSON Serialization (Alternative Format)
    // ----------------------------------------------------------------

    /**
     * Deserialize data from JSON format.
     *
     * @param string $json  The JSON-encoded data.
     * @param bool   $assoc Return associative arrays instead of objects.
     * @param int    $flags JSON decoding flags.
     *
     * @return mixed The decoded data.
     *
     * @throws SerializationException If JSON decoding fails.
     */
    public function fromJson(string $json, bool $assoc = true, int $flags = JSON_THROW_ON_ERROR) : mixed
    {
        try {
            return json_decode(json: $json, associative: $assoc, depth: 512, flags: $flags);
        } catch (JsonException $e) {
            throw SerializationException::jsonDecodeFailed(reason: $e->getMessage(), previous: $e);
        }
    }

    /**
     * Set allowed classes for deserialization.
     *
     * @param array<int, string> $classes Fully qualified class names.
     *
     * @return self Fluent interface.
     */
    public function setAllowedClasses(array $classes) : self
    {
        $this->allowedClasses = $classes;

        return $this;
    }

    // ----------------------------------------------------------------
    // Configuration
    // ----------------------------------------------------------------

    /**
     * Enable or disable compression.
     *
     * @param bool $enabled Enable compression.
     *
     * @return self Fluent interface.
     */
    public function setCompression(bool $enabled) : self
    {
        $this->compressionEnabled = $enabled;

        return $this;
    }

    /**
     * Set compression threshold.
     *
     * @param int $bytes Minimum size in bytes for compression.
     *
     * @return self Fluent interface.
     */
    public function setCompressionThreshold(int $bytes) : self
    {
        $this->compressionThreshold = $bytes;

        return $this;
    }

    /**
     * Enable or disable integrity checking.
     *
     * @param bool $enabled Enable integrity checks.
     *
     * @return self Fluent interface.
     */
    public function setIntegrityCheck(bool $enabled) : self
    {
        $this->integrityCheckEnabled = $enabled;

        return $this;
    }

    /**
     * Check if data is serialized.
     *
     * @param string $data The data to check.
     *
     * @return bool True if data appears to be serialized.
     */
    public function isSerialized(string $data) : bool
    {
        // Check for PHP serialization format
        if (preg_match(pattern: '/^([adObis]):/', subject: $data)) {
            return true;
        }

        // Check for our compressed format
        if (str_starts_with(haystack: $data, needle: 'GZIP:')) {
            return true;
        }

        // Check for our integrity-checked format
        if ($this->integrityCheckEnabled && str_contains(haystack: $data, needle: ':')) {
            return true;
        }

        return false;
    }

    // ----------------------------------------------------------------
    // Utility Methods
    // ----------------------------------------------------------------

    /**
     * Get the size of serialized data.
     *
     * @param mixed $data The data to measure.
     *
     * @return int Size in bytes.
     */
    public function getSerializedSize(mixed $data) : int
    {
        return strlen(string: $this->serialize(data: $data));
    }

    /**
     * Serialize data with optional compression and integrity check.
     *
     * @param mixed $data The data to serialize.
     *
     * @return string Serialized (and optionally compressed) data.
     *
     * @throws SerializationException If serialization fails.
     */
    public function serialize(mixed $data) : string
    {
        try {
            // Step 1: Serialize the data
            $serialized = serialize(value: $data);

            // Step 2: Apply compression if enabled and data is large enough
            if ($this->compressionEnabled && strlen(string: $serialized) > $this->compressionThreshold) {
                $compressed = gzcompress(data: $serialized, level: 6);
                if ($compressed === false) {
                    throw SerializationException::compressionFailed();
                }
                $serialized = 'GZIP:' . $compressed;
            }

            // Step 3: Add integrity checksum if enabled
            if ($this->integrityCheckEnabled) {
                $checksum   = hash(algo: 'sha256', data: $serialized);
                $serialized = $checksum . ':' . $serialized;
            }

            return $serialized;
        } catch (Throwable $e) {
            throw SerializationException::serializationFailed(reason: $e->getMessage(), previous: $e);
        }
    }

    /**
     * Check if data would be compressed.
     *
     * @param mixed $data The data to check.
     *
     * @return bool True if data would be compressed.
     */
    public function wouldCompress(mixed $data) : bool
    {
        if (! $this->compressionEnabled) {
            return false;
        }

        $serialized = serialize(value: $data);

        return strlen(string: $serialized) > $this->compressionThreshold;
    }
}
