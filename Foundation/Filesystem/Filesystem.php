<?php

declare(strict_types=1);

namespace Avax\Filesystem;

use Avax\Filesystem\Disks\Disk;
use Avax\Filesystem\Disks\ResolveDisk;
use Avax\Filesystem\Files\ReadFile;
use Avax\Filesystem\Files\WriteFile;
use Avax\Filesystem\Files\AppendToFile;
use Avax\Filesystem\Files\DeleteFile;
use Avax\Filesystem\Files\ReadFileLastModifiedAt;
use Avax\Filesystem\Directories\EnsureDirectoryExists;
use Avax\Filesystem\Paths\PathExists;
use Exception;

/**
 * Root public facade for filesystem operations.
 *
 * This is the single entry point for all filesystem operations.
 * Delegates to capability-specific action owners.
 */
class Filesystem implements FilesystemInterface
{
    private Disk $disk;

    public function __construct(Disk $disk) { $this->disk = $disk; }

    public function get(string $path) : string
    {
        return (new ReadFile(disk: $this->disk))->execute(path: $path);
    }

    public function put(string $path, string $content) : void
    {
        (new WriteFile(disk: $this->disk))->execute(path: $path, content: $content);
    }

    public function exists(string $path) : bool
    {
        return (new PathExists())->execute(path: $path);
    }

    public function delete(string $path) : void
    {
        (new DeleteFile(disk: $this->disk))->execute(path: $path);
    }

    public function lastModified(string $path) : int|null
    {
        return (new ReadFileLastModifiedAt(disk: $this->disk))->execute(path: $path);
    }

    public function ensureDirectory(string $path) : void
    {
        (new EnsureDirectoryExists(disk: $this->disk))->execute(path: $path);
    }

    public function append(string $path, string $content) : void
    {
        (new AppendToFile(disk: $this->disk))->execute(path: $path, content: $content);
    }

    public static function disk(string|null $name = null) : Disk
    {
        return (new ResolveDisk())->execute(name: $name);
    }
}