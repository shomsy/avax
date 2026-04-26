<?php

declare(strict_types=1);

namespace Avax\Filesystem\System\Foundation\Implementation;

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
use Avax\Filesystem\Files\WriteToFile;
use Avax\Filesystem\Filesystem as FilesystemInterface;
use Avax\Filesystem\Paths\VerifyPathSecurity;

/**
 * Delegates all filesystem operations to local disk through action-owner classes.
 */
final class LocalFilesystem implements FilesystemInterface
{
    public function __construct(private readonly ResolveDisk $diskResolver = new ResolveDisk()) {}

    public function get(string $path) : string
    {
        return (new ReadFile())(path: $path);
    }

    public function put(string $path, string $content) : void
    {
        (new WriteToFile())(path: $path, content: $content);
    }

    public function append(string $path, string $content) : void
    {
        (new AppendToFile())(path: $path, content: $content);
    }

    public function copy(string $source, string $destination) : void
    {
        (new CopyFile())(source: $source, destination: $destination);
    }

    public function move(string $source, string $destination) : void
    {
        (new MoveFile())(source: $source, destination: $destination);
    }

    public function exists(string $path) : bool
    {
        return file_exists(filename: $path);
    }

    public function delete(string $path) : void
    {
        (new DeleteFile())(path: $path);
    }

    public function lastModified(string $path) : int|null
    {
        return (new ReadFileLastModifiedAt())(path: $path);
    }

    public function ensureDirectory(string $path) : void
    {
        (new EnsureDirectoryExists())(path: $path);
    }

    public function ensureDirectoryIsWritable(string $path) : bool
    {
        return (new EnsureDirectoryIsWritable())(path: $path);
    }

    public function createDirectory(string $path, int $permissions = 0755) : void
    {
        (new CreateDirectory())(path: $path, permissions: $permissions);
    }

    public function deleteDirectory(string $path) : void
    {
        (new DeleteDirectory())(path: $path);
    }

    public function clearDirectory(string $path) : void
    {
        (new ClearDirectory())(path: $path);
    }

    public function listFiles(string $path) : array
    {
        return (new ListDirectoryFiles())(path: $path);
    }

    public function isWritable(string $path) : bool
    {
        return is_writable(filename: $path);
    }

    public function setPermissions(string $path, int $permissions) : bool
    {
        return chmod(filename: $path, permissions: $permissions);
    }

    public function hasPermission(string $path, int $permissions) : bool
    {
        $current = fileperms(filename: $path);

        return ($current & $permissions) === $permissions;
    }

    public function disk(string|null $name = null) : Disk
    {
        return $this->diskResolver->resolve(name: $name);
    }
}
