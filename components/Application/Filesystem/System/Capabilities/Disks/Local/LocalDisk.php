<?php

declare(strict_types=1);

namespace Avax\Components\Application\Filesystem\System\Capabilities\Disks\Local;

use Avax\Components\Application\Filesystem\System\Capabilities\Disks\Disk;
use FilesystemIterator;
use Override;
use RuntimeException;
use SplFileInfo;
use Throwable;

final readonly class LocalDisk implements Disk
{
    #[Override]
    public function read(string $path) : string
    {
        if (! file_exists($path) || ! is_readable($path)) {
            throw new RuntimeException('File not found or not readable: ' . $path);
        }

        $content = file_get_contents($path);
        if ($content === false) {
            throw new RuntimeException('Failed to read file: ' . $path);
        }

        return $content;
    }

    #[Override]
    public function write(string $path, string $content, bool $append = false) : bool
    {
        $directory = dirname($path);
        if (! is_dir($directory)) {
            mkdir($directory, 0o755, true);
        }

        $flags = $append ? FILE_APPEND | LOCK_EX : 0;

        return file_put_contents($path, $content, $flags) !== false;
    }

    #[Override]
    public function copy(string $source, string $destination) : bool
    {
        if (! file_exists($source)) {
            throw new RuntimeException('Source file not found: ' . $source);
        }

        $directory = dirname($destination);
        if (! is_dir($directory)) {
            mkdir($directory, 0o755, true);
        }

        return copy($source, $destination);
    }

    #[Override]
    public function move(string $source, string $destination) : bool
    {
        if (! file_exists($source)) {
            throw new RuntimeException('Source file not found: ' . $source);
        }

        $directory = dirname($destination);
        if (! is_dir($directory)) {
            mkdir($directory, 0o755, true);
        }

        return rename($source, $destination);
    }

    #[Override]
    public function delete(string $path) : bool
    {
        return file_exists($path) ? unlink($path) : true;
    }

    #[Override]
    public function exists(string $path) : bool
    {
        return file_exists($path);
    }

    #[Override]
    public function lastModified(string $path) : int|null
    {
        return file_exists($path) ? (filemtime($path) ?: null) : null;
    }

    #[Override]
    public function createDirectory(string $path, int $permissions = 0o755) : bool
    {
        return is_dir($path) || mkdir($path, $permissions, true);
    }

    #[Override]
    public function deleteDirectory(string $path) : bool
    {
        if (! is_dir($path)) {
            return true;
        }

        $this->clear($path);

        return rmdir($path);
    }

    #[Override]
    public function clear(string $path) : bool
    {
        if (! is_dir($path)) {
            return false;
        }

        foreach (new FilesystemIterator($path, FilesystemIterator::SKIP_DOTS) as $item) {
            $itemPath = $item->getPathname();
            $item->isDir() ? $this->deleteDirectory($itemPath) : $this->delete($itemPath);
        }

        return true;
    }

    #[Override]
    public function isWritable(string $path) : bool
    {
        return is_writable($path);
    }

    #[Override]
    public function setPermissions(string $path, int $permissions) : bool
    {
        return file_exists($path) && chmod($path, $permissions);
    }

    #[Override]
    public function listFiles(string $path) : array
    {
        if (! is_dir($path) || ! is_readable($path)) {
            return [];
        }

        try {
            $iterator = new FilesystemIterator($path, FilesystemIterator::SKIP_DOTS);

            return array_map(
                static fn (SplFileInfo $file) : string => $file->getPathname(),
                iterator_to_array($iterator, false),
            );
        } catch (Throwable) {
            return [];
        }
    }
}
