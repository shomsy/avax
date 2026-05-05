<?php

declare(strict_types=1);

namespace Avax\Components\Application\Filesystem\System\PublicSurface;

use Avax\Components\Application\Filesystem\System\Capabilities\Storage\StorageInterface;
use RuntimeException;

/**
 * Storage Public Surface.
 *
 * Main entry point for filesystem operations.
 * Delegated to internal storage capabilities.
 */
final class FilesystemStorage
{
    private static ?StorageInterface $storage = null;

    public static function setStorage(StorageInterface $storage): void
    {
        self::$storage = $storage;
    }

    public static function get(string $path): string
    {
        return self::getStorage()->read($path);
    }

    private static function getStorage(): StorageInterface
    {
        if (!self::$storage instanceof StorageInterface) {
            throw new RuntimeException('Storage not configured. Call Storage::setStorage() first.');
        }

        return self::$storage;
    }

    public static function put(string $path, string $content): bool
    {
        return self::getStorage()->write($path, $content);
    }

    public static function append(string $path, string $content): bool
    {
        return self::getStorage()->write($path, $content, true);
    }

    public static function exists(string $path): bool
    {
        return self::getStorage()->exists($path);
    }

    public static function delete(string $path): bool
    {
        return self::getStorage()->delete($path);
    }

    public static function makeDirectory(string $path, int $permissions = 0o755): bool
    {
        return self::getStorage()->createDirectory($path, $permissions);
    }

    public static function deleteDirectory(string $path): bool
    {
        return self::getStorage()->deleteDirectory($path);
    }

    public static function copy(string $source, string $destination): bool
    {
        return self::getStorage()->copy($source, $destination);
    }

    public static function move(string $source, string $destination): bool
    {
        return self::getStorage()->move($source, $destination);
    }

    public static function lastModified(string $path): ?int
    {
        return self::getStorage()->lastModified($path);
    }

    public static function isWritable(string $path): bool
    {
        return self::getStorage()->isWritable($path);
    }

    public static function listFiles(string $path): array
    {
        return self::getStorage()->listFiles($path);
    }
}
