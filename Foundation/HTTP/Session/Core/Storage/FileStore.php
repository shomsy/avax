<?php

declare(strict_types=1);

namespace Avax\HTTP\Session\Core\Storage;

use Avax\Filesystem\Storage\FileStorageInterface;
use Avax\HTTP\Session\Shared\Contracts\Storage\StoreInterface;
use RuntimeException;
use Throwable;

/**
 * FileStore - Enterprise-Grade File-Based Session Storage
 * ============================================================
 *
 * Enhanced file storage with metadata support including:
 * - Original key preservation (no MD5 key loss)
 * - TTL/expiration support
 * - Creation and modification timestamps
 * - Namespace-based operations
 *
 * File Format:
 * ```php
 * [
 *     'key' => 'user_id',           // Original key
 *     'value' => 'user123',         // Actual value
 *     'created_at' => 1702234567,   // Creation timestamp
 *     'expires_at' => 1702238167,   // Expiration (null = never)
 * ]
 * ```
 */
final readonly class FileStore implements StoreInterface
{
    public function __construct(
        private FileStorageInterface $storage,
        private string               $directory = 'sessions'
    ) {}

    /**
     * Save a session value with metadata.
     *
     * @param string   $key   The session key.
     * @param mixed    $value The value to store.
     * @param int|null $ttl   Time-to-live in seconds (null = never expires).
     */
    public function put(string $key, mixed $value, int|null $ttl = null) : void
    {
        $path = $this->pathFor(key: $key);

        $metadata = [
            'key'        => $key,
            'value'      => $value,
            'created_at' => time(),
            'expires_at' => $ttl ? time() + $ttl : null,
        ];

        try {
            $this->storage->write(
                path   : $path,
                content: serialize(value: $metadata)
            );
        } catch (Throwable $e) {
            throw new RuntimeException(
                message : "Failed to write session key '{$key}': " . $e->getMessage(),
                code    : 0,
                previous: $e
            );
        }
    }

    /**
     * Build a consistent file path for the given key.
     *
     * Uses MD5 hash for filesystem safety while preserving original key in metadata.
     *
     * @param string $key The session key.
     *
     * @return string The file path.
     */
    private function pathFor(string $key) : string
    {
        return "{$this->directory}/" . md5(string: $key) . '.sess';
    }

    /**
     * Retrieve a session value.
     *
     * Automatically checks expiration and deletes expired entries.
     *
     * @param string $key     Session key.
     * @param mixed  $default Default value if key doesn't exist or expired.
     *
     * @return mixed The session value or default.
     */
    public function get(string $key, mixed $default = null) : mixed
    {
        $path = $this->pathFor(key: $key);

        if (! $this->storage->exists(path: $path)) {
            return $default;
        }

        try {
            $metadata = unserialize(data: $this->storage->read(path: $path));

            // Check expiration
            if (isset($metadata['expires_at']) && $metadata['expires_at'] !== null) {
                if (time() > $metadata['expires_at']) {
                    // Expired - delete and return default
                    $this->delete(key: $key);

                    return $default;
                }
            }

            return $metadata['value'] ?? $default;
        } catch (Throwable $e) {
            throw new RuntimeException(
                message : "Failed to read session key '{$key}': " . $e->getMessage(),
                code    : 0,
                previous: $e
            );
        }
    }

    /**
     * Delete a specific session key.
     *
     * @param string $key The session key.
     */
    public function delete(string $key) : void
    {
        try {
            $this->storage->delete(path: $this->pathFor(key: $key));
        } catch (Throwable $e) {
            throw new RuntimeException(
                message : "Failed to delete session key '{$key}': " . $e->getMessage(),
                code    : 0,
                previous: $e
            );
        }
    }

    /**
     * Check if a key exists and is not expired.
     *
     * @param string $key The session key.
     *
     * @return bool True if exists and not expired.
     */
    public function has(string $key) : bool
    {
        $path = $this->pathFor(key: $key);

        if (! $this->storage->exists(path: $path)) {
            return false;
        }

        try {
            $metadata = unserialize(data: $this->storage->read(path: $path));

            // Check expiration
            if (isset($metadata['expires_at']) && $metadata['expires_at'] !== null) {
                if (time() > $metadata['expires_at']) {
                    $this->delete(key: $key);

                    return false;
                }
            }

            return true;
        } catch (Throwable) {
            return false;
        }
    }

    /**
     * Retrieve all session data with original keys.
     *
     * Returns array with original keys (not MD5 hashes).
     * Automatically filters out expired entries.
     *
     * @return array<string, mixed> All session data.
     */
    public function all() : array
    {
        try {
            $files = $this->storage->listFiles(directory: $this->directory);
            $data  = [];

            foreach ($files as $file) {
                $content  = $this->storage->read(path: $file);
                $metadata = unserialize(data: $content);

                // Skip expired entries
                if (isset($metadata['expires_at']) && $metadata['expires_at'] !== null) {
                    if (time() > $metadata['expires_at']) {
                        $this->storage->delete(path: $file);

                        continue;
                    }
                }

                // Use original key, not MD5 hash
                $originalKey        = $metadata['key'] ?? basename(path: $file, suffix: '.sess');
                $data[$originalKey] = $metadata['value'] ?? null;
            }

            return $data;
        } catch (Throwable $e) {
            throw new RuntimeException(
                message : 'Failed to read all session data: ' . $e->getMessage(),
                code    : 0,
                previous: $e
            );
        }
    }

    /**
     * Delete all session data.
     */
    public function flush() : void
    {
        try {
            $this->storage->deleteDirectory(directory: $this->directory);
        } catch (Throwable $e) {
            throw new RuntimeException(
                message : 'Failed to flush session directory: ' . $e->getMessage(),
                code    : 0,
                previous: $e
            );
        }
    }

    /**
     * Flush all keys matching a namespace prefix.
     *
     * Example: flushNamespace('cart') deletes 'cart.items', 'cart.total', etc.
     *
     * @param string $prefix The namespace prefix.
     */
    public function flushNamespace(string $prefix) : void
    {
        try {
            $files = $this->storage->listFiles(directory: $this->directory);

            foreach ($files as $file) {
                $content  = $this->storage->read(path: $file);
                $metadata = unserialize(data: $content);

                $originalKey = $metadata['key'] ?? '';

                // Delete if key starts with prefix
                if (str_starts_with(haystack: $originalKey, needle: $prefix . '.')) {
                    $this->storage->delete(path: $file);
                }
            }
        } catch (Throwable $e) {
            throw new RuntimeException(
                message : "Failed to flush namespace '{$prefix}': " . $e->getMessage(),
                code    : 0,
                previous: $e
            );
        }
    }
}
