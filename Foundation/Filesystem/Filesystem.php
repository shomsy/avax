<?php

declare(strict_types=1);

namespace Avax\Filesystem;

use Avax\Filesystem\Directories\ClearDirectory;
use Avax\Filesystem\Directories\CreateDirectory;
use Avax\Filesystem\Directories\DeleteDirectory;
use Avax\Filesystem\Directories\EnsureDirectoryExists;
use Avax\Filesystem\Directories\EnsureDirectoryIsWritable;
use Avax\Filesystem\Directories\ListDirectoryFiles;
use Avax\Filesystem\Disks\Disk;
use Avax\Filesystem\Disks\ResolveDisk;
use Avax\Filesystem\Files\AppendToFile;
use Avax\Filesystem\Files\CopyFile;
use Avax\Filesystem\Files\DeleteFile;
use Avax\Filesystem\Files\MoveFile;
use Avax\Filesystem\Files\ReadFile;
use Avax\Filesystem\Files\ReadFileLastModifiedAt;
use Avax\Filesystem\Files\WriteFile;

/**
 * Root public facade for filesystem operations.
 *
 * This is the single entry point for all filesystem operations.
 * Delegates to capability-specific action owners.
 */
final class Filesystem implements FilesystemInterface
{
    public function __construct(private readonly Disk $disk) {}

    public function get(string $path) : string
    {
        return new ReadFile(disk: $this->disk)->execute(path: $path);
    }

    public function put(string $path, string $content) : void
    {
        new WriteFile(disk: $this->disk)->execute(path: $path, content: $content);
    }

    public function append(string $path, string $content) : void
    {
        new AppendToFile(disk: $this->disk)->execute(path: $path, content: $content);
    }

    public function copy(string $source, string $destination) : void
    {
        new CopyFile(disk: $this->disk)->execute(source: $source, destination: $destination);
    }

    public function move(string $source, string $destination) : void
    {
        new MoveFile(disk: $this->disk)->execute(source: $source, destination: $destination);
    }

    public function exists(string $path) : bool
    {
        return $this->disk->exists(path: $path);
    }

    public function delete(string $path) : void
    {
        new DeleteFile(disk: $this->disk)->execute(path: $path);
    }

    public function lastModified(string $path) : int|null
    {
        return new ReadFileLastModifiedAt(disk: $this->disk)->execute(path: $path);
    }

    public function ensureDirectory(string $path) : void
    {
        new EnsureDirectoryExists(disk: $this->disk)->execute(path: $path);
    }

    public function ensureDirectoryIsWritable(string $path) : bool
    {
        return new EnsureDirectoryIsWritable(disk: $this->disk)->execute(path: $path);
    }

    public function createDirectory(string $path, int $permissions = 0755) : void
    {
        new CreateDirectory(disk: $this->disk)->execute(path: $path, permissions: $permissions);
    }

    public function deleteDirectory(string $path) : void
    {
        new DeleteDirectory(disk: $this->disk)->execute(path: $path);
    }

    public function clearDirectory(string $path) : void
    {
        new ClearDirectory(disk: $this->disk)->execute(path: $path);
    }

    public function listFiles(string $path) : array
    {
        return new ListDirectoryFiles(disk: $this->disk)->execute(path: $path);
    }

    public function isWritable(string $path) : bool
    {
        return $this->disk->isWritable(path: $path);
    }

    public function setPermissions(string $path, int $permissions) : bool
    {
        return $this->disk->setPermissions(path: $path, permissions: $permissions);
    }

    public function hasPermission(string $path, int $permissions) : bool
    {
        return $this->disk->hasPermission(path: $path, permissions: $permissions);
    }

    public static function disk(string|null $name = null) : Disk
    {
        return new ResolveDisk()->execute(name: $name);
    }
}
