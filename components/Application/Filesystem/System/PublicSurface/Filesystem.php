<?php

declare(strict_types=1);

namespace Avax\Components\Application\Filesystem\System\PublicSurface;

use Avax\Components\Application\Filesystem\System\Flows\AppendToFile\AppendToFile;
use Avax\Components\Application\Filesystem\System\Flows\CheckPathExists\CheckPathExists;
use Avax\Components\Application\Filesystem\System\Flows\ClearDirectory\ClearDirectory;
use Avax\Components\Application\Filesystem\System\Flows\CopyFile\CopyFile;
use Avax\Components\Application\Filesystem\System\Flows\CreateDirectory\CreateDirectory;
use Avax\Components\Application\Filesystem\System\Flows\DeleteDirectory\DeleteDirectory;
use Avax\Components\Application\Filesystem\System\Flows\DeleteFile\DeleteFile;
use Avax\Components\Application\Filesystem\System\Flows\ListDirectory\ListDirectory;
use Avax\Components\Application\Filesystem\System\Flows\MoveFile\MoveFile;
use Avax\Components\Application\Filesystem\System\Flows\ReadFile\ReadFile;
use Avax\Components\Application\Filesystem\System\Flows\WriteFile\WriteFile;

final readonly class Filesystem
{
    public function __construct(
        private ReadFile        $readFile = new ReadFile(),
        private WriteFile       $writeFile = new WriteFile(),
        private AppendToFile    $appendToFile = new AppendToFile(),
        private CopyFile        $copyFile = new CopyFile(),
        private MoveFile        $moveFile = new MoveFile(),
        private DeleteFile      $deleteFile = new DeleteFile(),
        private CreateDirectory $createDirectory = new CreateDirectory(),
        private DeleteDirectory $deleteDirectory = new DeleteDirectory(),
        private ClearDirectory  $clearDirectory = new ClearDirectory(),
        private ListDirectory   $listDirectory = new ListDirectory(),
        private CheckPathExists $checkPathExists = new CheckPathExists(),
    ) {}

    public function read(string $path): string
    {
        return $this->readFile->execute($path);
    }

    public function write(string $path, string $content) : bool
    {
        return $this->writeFile->execute($path, $content);
    }

    public function append(string $path, string $content) : bool
    {
        return $this->appendToFile->execute($path, $content);
    }

    public function copy(string $source, string $destination): bool
    {
        return $this->copyFile->execute($source, $destination);
    }

    public function move(string $source, string $destination): bool
    {
        return $this->moveFile->execute($source, $destination);
    }

    public function delete(string $path): bool
    {
        return $this->deleteFile->execute($path);
    }

    public function exists(string $path): bool
    {
        return $this->checkPathExists->execute($path);
    }

    public function createDirectory(string $path, int $permissions = 0o755): bool
    {
        return $this->createDirectory->execute($path, $permissions);
    }

    public function deleteDirectory(string $path): bool
    {
        return $this->deleteDirectory->execute($path);
    }

    public function clearDirectory(string $path): bool
    {
        return $this->clearDirectory->execute($path);
    }

    /**
     * @return list<string>
     */
    public function listDirectory(string $path) : array
    {
        return $this->listDirectory->execute($path);
    }

    public function isReadable(string $path) : bool
    {
        return file_exists($path) && is_readable($path);
    }

    public function isWritable(string $path): bool
    {
        if (file_exists($path)) {
            return is_writable($path);
        }

        return is_writable(dirname($path));
    }

    public function permissions(string $path) : ?int
    {
        if (! file_exists($path)) {
            return null;
        }

        return fileperms($path) & 0o777;
    }

    public function changePermissions(string $path, int $permissions) : bool
    {
        if (! file_exists($path)) {
            return false;
        }

        return chmod($path, $permissions);
    }
}