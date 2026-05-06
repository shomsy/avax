<?php

declare(strict_types=1);

namespace Avax\Components\Application\Filesystem\System\Capabilities\Storage;

/**
 * Storage Capability.
 *
 * Handles low-level filesystem operations (get, put, delete, etc.)
 * using the local disk adapter.
 */
final class LocalStorage
{
    public function get(string $path): string
    {
        return file_get_contents($path);
    }

    public function put(string $path, string $content): void
    {
        $directory = dirname($path);
        if (! is_dir($directory)) {
            mkdir($directory, 0o755, true);
        }

        file_put_contents($path, $content);
    }

    public function delete(string $path): void
    {
        if ($this->exists($path)) {
            unlink($path);
        }
    }

    public function exists(string $path): bool
    {
        return file_exists($path);
    }

    public function createDirectory(string $path, int $permissions = 0o755): void
    {
        if (! is_dir($path)) {
            mkdir($path, $permissions, true);
        }
    }
}
