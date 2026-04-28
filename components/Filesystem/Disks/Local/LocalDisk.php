<?php

declare(strict_types=1);

namespace Avax\Components\Filesystem\Disks\Local;

use Avax\Components\Filesystem\Directories\DirectoryClearFailed;
use Avax\Components\Filesystem\Directories\DirectoryCreateFailed;
use Avax\Components\Filesystem\Directories\DirectoryDeleteFailed;
use Avax\Components\Filesystem\Disks\Disk;
use Avax\Components\Filesystem\Files\FileCopyFailed;
use Avax\Components\Filesystem\Files\FileDeleteFailed as FileDeleteFailedException;
use Avax\Components\Filesystem\Files\FileMoveFailed;
use Avax\Components\Filesystem\Files\FileNotFound;
use Avax\Components\Filesystem\Files\FileWriteFailed;
use FilesystemIterator;
use RuntimeException;
use SplFileInfo;
use Throwable;

readonly class LocalDisk implements Disk
{
    public function read(string $path) : string
    {
        if (! file_exists(filename: $path)) {
            throw new FileNotFound(path: $path);
        }

        if (! is_readable(filename: $path)) {
            throw new FileNotFound(path: $path);
        }

        $content = file_get_contents(filename: $path);
        if ($content === false) {
            throw new FileNotFound(path: $path);
        }

        return $content;
    }

    public function write(string $path, string $content, bool $append = false) : bool
    {
        $directory = dirname(path: $path);
        if (! is_dir(filename: $directory) && ! mkdir(directory: $directory, permissions: 0755, recursive: true) && ! is_dir(filename: $directory)) {
            throw new DirectoryCreateFailed(path: $directory);
        }

        $flags = $append ? FILE_APPEND | LOCK_EX : 0;
        if (file_put_contents(filename: $path, data: $content . PHP_EOL, flags: $flags) === false) {
            throw new FileWriteFailed(path: $path);
        }

        return true;
    }

    public function copy(string $source, string $destination) : bool
    {
        if (! file_exists(filename: $source)) {
            throw new FileNotFound(path: $source);
        }

        $directory = dirname(path: $destination);
        if (! is_dir(filename: $directory) && ! mkdir(directory: $directory, permissions: 0755, recursive: true) && ! is_dir(filename: $directory)) {
            throw new DirectoryCreateFailed(path: $directory);
        }

        if (! copy(from: $source, to: $destination)) {
            throw new FileCopyFailed(path: $destination);
        }

        return true;
    }

    public function move(string $source, string $destination) : bool
    {
        if (! file_exists(filename: $source)) {
            throw new FileNotFound(path: $source);
        }

        $directory = dirname(path: $destination);
        if (! is_dir(filename: $directory) && ! mkdir(directory: $directory, permissions: 0755, recursive: true) && ! is_dir(filename: $directory)) {
            throw new DirectoryCreateFailed(path: $directory);
        }

        if (! rename(from: $source, to: $destination)) {
            throw new FileMoveFailed(path: $destination);
        }

        return true;
    }

    public function exists(string $path) : bool
    {
        return file_exists(filename: $path);
    }

    public function lastModified(string $path) : int|null
    {
        if (! file_exists(filename: $path)) {
            return null;
        }

        $mtime = filemtime(filename: $path);

        return $mtime !== false ? $mtime : null;
    }

    public function createDirectory(string $path, int $permissions = 0755) : bool
    {
        if (is_dir(filename: $path)) {
            return true;
        }

        if (! mkdir(directory: $path, permissions: $permissions, recursive: true)) {
            throw new DirectoryCreateFailed(path: $path);
        }

        return true;
    }

    public function deleteDirectory(string $path) : bool
    {
        if (! is_dir(filename: $path)) {
            return true;
        }

        $this->clear(path: $path);

        if (! rmdir(directory: $path)) {
            throw new DirectoryDeleteFailed(path: $path);
        }

        return true;
    }

    public function clear(string $path) : bool
    {
        if (! is_dir(filename: $path)) {
            throw new DirectoryClearFailed(path: $path);
        }

        foreach (new FilesystemIterator(directory: $path, flags: FilesystemIterator::SKIP_DOTS) as $item) {
            $itemPath = $item->getPathname();

            if ($item->isDir()) {
                $this->deleteDirectory(path: $itemPath);
            } else {
                $this->delete(path: $itemPath);
            }
        }

        return true;
    }

    public function delete(string $path) : bool
    {
        if (! file_exists(filename: $path)) {
            return true;
        }

        if (! unlink(filename: $path)) {
            throw new FileDeleteFailedException(path: $path);
        }

        return true;
    }

    public function isWritable(string $path) : bool
    {
        return is_writable(filename: $path);
    }

    public function setPermissions(string $path, int $permissions) : bool
    {
        if (! file_exists(filename: $path)) {
            throw new RuntimeException(message: "Path does not exist: {$path}");
        }

        if (! chmod(filename: $path, permissions: $permissions)) {
            error_log(message: "Failed to set permissions on: {$path}");

            return false;
        }

        return true;
    }

    public function hasPermission(string $path, int $permissions) : bool
    {
        if (! file_exists(filename: $path)) {
            return false;
        }

        $actualPermissions = fileperms(filename: $path) & 0777;

        return $actualPermissions === $permissions;
    }

    public function listFiles(string $path) : array
    {
        if (! is_dir(filename: $path) || ! is_readable(filename: $path)) {
            return [];
        }

        try {
            $iterator = new FilesystemIterator(
                directory: $path,
                flags    : FilesystemIterator::SKIP_DOTS
            );

            return array_map(
                callback: static fn (SplFileInfo $file) => $file->getPathname(),
                array   : iterator_to_array(iterator: $iterator, preserve_keys: false)
            );
        } catch (Throwable) {
            return [];
        }
    }
}
