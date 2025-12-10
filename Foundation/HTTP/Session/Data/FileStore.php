<?php

declare(strict_types=1);

namespace Avax\HTTP\Session\Data;

use Avax\Filesystem\Storage\FileStorageInterface;
use Avax\HTTP\Session\Contracts\Storage\Store;
use RuntimeException;
use Throwable;

/**
 * Class FileStore
 *
 * 🧠 Purpose:
 * Adapter between Session and the underlying Foundation FileStorage system.
 *
 * Translates high-level session operations (put, get, forget, flush)
 * into low-level file operations (write, read, delete).
 *
 * No facades are used — everything runs through dependency injection.
 *
 * 💬 Think of it as:
 * “Session speaks human (put/get), FileStorage speaks technical (write/read).”
 * FileStore is the translator that connects them.
 */
final readonly class FileStore implements Store, StoreInterface
{
    public function __construct(
        private FileStorageInterface $storage,
        private string               $directory = 'sessions'
    ) {}

    /**
     * Save a session value.
     */
    public function put(string $key, mixed $value) : void
    {
        $path = $this->pathFor(key: $key);

        try {
            $this->storage->write(path: $path, content: serialize(value: $value));
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
     */
    private function pathFor(string $key) : string
    {
        return "{$this->directory}/" . md5(string: $key) . '.sess';
    }

    /**
     * Retrieve a session value.
     */
    public function get(string $key, mixed $default = null) : mixed
    {
        $path = $this->pathFor(key: $key);

        if (! $this->storage->exists(path: $path)) {
            return $default;
        }

        try {
            return unserialize(data: $this->storage->read(path: $path));
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
     */
    public function forget(string $key) : void
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
     * Alias for forget(), kept for interface compatibility.
     *
     * 💬 Think of this as “synonym” for forget().
     * Some APIs prefer `delete()` naming.
     */
    public function delete(string $key) : void
    {
        $this->forget(key: $key);
    }

    /**
     * Check if a key exists.
     */
    public function has(string $key) : bool
    {
        return $this->storage->exists(path: $this->pathFor(key: $key));
    }

    /**
     * Retrieve all session data currently stored.
     *
     * 🧠 Purpose:
     * Used by Recovery or Debug features to inspect the entire session snapshot.
     *
     * 💬 Think of it like “show me everything saved for this session”.
     */
    public function all() : array
    {
        try {
            $files = $this->storage->listFiles($this->directory);
            $data  = [];

            foreach ($files as $file) {
                $content = $this->storage->read(path: $file);

                // Reverse the MD5 naming — use file name (no extension) as key
                $key        = basename(path: $file, suffix: '.sess');
                $data[$key] = unserialize(data: $content);
            }

            return $data;
        } catch (Throwable $e) {
            throw new RuntimeException(
                message : "Failed to read all session data: " . $e->getMessage(),
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
                message : "Failed to flush session directory: " . $e->getMessage(),
                code    : 0,
                previous: $e
            );
        }
    }
}
