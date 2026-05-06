<?php

declare(strict_types=1);

namespace Avax\Components\Application\Filesystem\System\Foundation\Implementation;

use Avax\Components\Application\Filesystem\System\Capabilities\Disks\Disk;
use Avax\Components\Application\Filesystem\System\Capabilities\Disks\ResolveDisk;
use Avax\Components\Application\Filesystem\System\Flows\Directories\ClearDirectory;
use Avax\Components\Application\Filesystem\System\Flows\Directories\CreateDirectory;
use Avax\Components\Application\Filesystem\System\Flows\Directories\DeleteDirectory;
use Avax\Components\Application\Filesystem\System\Flows\Directories\EnsureDirectoryExists;
use Avax\Components\Application\Filesystem\System\Flows\Directories\EnsureDirectoryIsWritable;
use Avax\Components\Application\Filesystem\System\Flows\Directories\ListDirectoryFiles;
use Avax\Components\Application\Filesystem\System\Flows\Files\AppendToFile;
use Avax\Components\Application\Filesystem\System\Flows\Files\CopyFile;
use Avax\Components\Application\Filesystem\System\Flows\Files\DeleteFile;
use Avax\Components\Application\Filesystem\System\Flows\Files\MoveFile;
use Avax\Components\Application\Filesystem\System\Flows\Files\ReadFile;
use Avax\Components\Application\Filesystem\System\Flows\Files\ReadFileLastModifiedAt;
use Avax\Components\Application\Filesystem\System\Flows\Files\WriteFile;
use Avax\Components\Application\Filesystem\System\PublicSurface\FilesystemInterface;

/**
 * Delegates all filesystem operations to local disk through action-owner classes.
 */
readonly class LocalFilesystem implements FilesystemInterface
{
    public function __construct(private ResolveDisk $resolveDisk = new ResolveDisk())
    {
    }

    public function read(string $path) : string
    {
        return new ReadFile(disk: $this->disk())->execute(path: $path);
    }

    public function get(string $path) : string
    {
        return $this->read(path: $path);
    }

    public function disk(?string $name = null): Disk
    {
        return $this->resolveDisk->execute(name: $name);
    }

    public function write(string $path, string $content, bool $append = false) : bool
    {
        if ($append) {
            new AppendToFile(disk: $this->disk())->execute(path: $path, content: $content);

            return true;
        }

        new WriteFile(disk: $this->disk())->execute(path: $path, content: $content);

        return true;
    }

    public function put(string $path, string $content) : void
    {
        $this->write(path: $path, content: $content);
    }

    public function append(string $path, string $content): void
    {
        $this->write(path: $path, content: $content, append: true);
    }

    public function copy(string $source, string $destination) : bool
    {
        new CopyFile(disk: $this->disk())->execute(source: $source, destination: $destination);

        return true;
    }

    public function move(string $source, string $destination) : bool
    {
        new MoveFile(disk: $this->disk())->execute(source: $source, destination: $destination);

        return true;
    }

    public function exists(string $path): bool
    {
        return file_exists(filename: $path);
    }

    public function delete(string $path) : bool
    {
        new DeleteFile(disk: $this->disk())->execute(path: $path);

        return true;
    }

    public function lastModified(string $path): ?int
    {
        return new ReadFileLastModifiedAt(disk: $this->disk())->execute(path: $path);
    }

    public function ensureDirectory(string $path): void
    {
        new EnsureDirectoryExists(disk: $this->disk())->execute(path: $path);
    }

    public function ensureDirectoryIsWritable(string $path): bool
    {
        return new EnsureDirectoryIsWritable(disk: $this->disk())->execute(path: $path);
    }

    public function createDirectory(string $path, int $permissions = 0o755) : bool
    {
        new CreateDirectory(disk: $this->disk())->execute(path: $path, permissions: $permissions);

        return true;
    }

    public function deleteDirectory(string $path) : bool
    {
        new DeleteDirectory(disk: $this->disk())->execute(path: $path);

        return true;
    }

    public function clearDirectory(string $path) : bool
    {
        new ClearDirectory(disk: $this->disk())->execute(path: $path);

        return true;
    }

    public function listFiles(string $path): array
    {
        return new ListDirectoryFiles(disk: $this->disk())->execute(path: $path);
    }

    public function isWritable(string $path): bool
    {
        return is_writable(filename: $path);
    }

    public function setPermissions(string $path, int $permissions): bool
    {
        return chmod(filename: $path, permissions: $permissions);
    }

    public function hasPermission(string $path, int $permissions): bool
    {
        $current = fileperms(filename: $path);

        return ($current & $permissions) === $permissions;
    }
}
