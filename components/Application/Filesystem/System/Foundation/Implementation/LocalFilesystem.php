<?php

declare(strict_types=1);

namespace Avax\Components\Application\Filesystem\System\Foundation\Implementation;

use Avax\Components\Application\Filesystem\Directories\ClearDirectory;
use Avax\Components\Application\Filesystem\Directories\CreateDirectory;
use Avax\Components\Application\Filesystem\Directories\DeleteDirectory;
use Avax\Components\Application\Filesystem\Directories\EnsureDirectoryExists;
use Avax\Components\Application\Filesystem\Directories\EnsureDirectoryIsWritable;
use Avax\Components\Application\Filesystem\Directories\ListDirectoryFiles;
use Avax\Components\Application\Filesystem\System\Capabilities\Disks\Disk;
use Avax\Components\Application\Filesystem\Disks\ResolveDisk;
use Avax\Components\Application\Filesystem\Files\AppendToFile;
use Avax\Components\Application\Filesystem\Files\CopyFile;
use Avax\Components\Application\Filesystem\Files\DeleteFile;
use Avax\Components\Application\Filesystem\Files\MoveFile;
use Avax\Components\Application\Filesystem\Files\ReadFile;
use Avax\Components\Application\Filesystem\Files\ReadFileLastModifiedAt;
use Avax\Components\Application\Filesystem\Files\WriteFile;
use Avax\Components\Application\Filesystem\Filesystem as FilesystemInterface;

/**
 * Delegates all filesystem operations to local disk through action-owner classes.
 */
readonly class LocalFilesystem implements FilesystemInterface
{
    public function __construct(private ResolveDisk $resolveDisk = new ResolveDisk()) {}

    public function get(string $path) : string
    {
        return new ReadFile(disk: $this->disk())->execute(path: $path);
    }

    public function disk(?string $name = null) : Disk
    {
        return $this->resolveDisk->execute(name: $name);
    }

    public function put(string $path, string $content) : void
    {
        new WriteFile(disk: $this->disk())->execute(path: $path, content: $content);
    }

    public function append(string $path, string $content) : void
    {
        new AppendToFile(disk: $this->disk())->execute(path: $path, content: $content);
    }

    public function copy(string $source, string $destination) : void
    {
        new CopyFile(disk: $this->disk())->execute(source: $source, destination: $destination);
    }

    public function move(string $source, string $destination) : void
    {
        new MoveFile(disk: $this->disk())->execute(source: $source, destination: $destination);
    }

    public function exists(string $path) : bool
    {
        return file_exists(filename: $path);
    }

    public function delete(string $path) : void
    {
        new DeleteFile(disk: $this->disk())->execute(path: $path);
    }

    public function lastModified(string $path) : ?int
    {
        return new ReadFileLastModifiedAt(disk: $this->disk())->execute(path: $path);
    }

    public function ensureDirectory(string $path) : void
    {
        new EnsureDirectoryExists(disk: $this->disk())->execute(path: $path);
    }

    public function ensureDirectoryIsWritable(string $path) : bool
    {
        return new EnsureDirectoryIsWritable(disk: $this->disk())->execute(path: $path);
    }

    public function createDirectory(string $path, int $permissions = 0o755) : void
    {
        new CreateDirectory(disk: $this->disk())->execute(path: $path, permissions: $permissions);
    }

    public function deleteDirectory(string $path) : void
    {
        new DeleteDirectory(disk: $this->disk())->execute(path: $path);
    }

    public function clearDirectory(string $path) : void
    {
        new ClearDirectory(disk: $this->disk())->execute(path: $path);
    }

    public function listFiles(string $path) : array
    {
        return new ListDirectoryFiles(disk: $this->disk())->execute(path: $path);
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
}
