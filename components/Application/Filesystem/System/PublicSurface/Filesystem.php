<?php

declare(strict_types=1);

namespace Avax\Components\Application\Filesystem\System\PublicSurface;

use Avax\Components\Application\Filesystem\System\Capabilities\Disks\Disk;
use Override;

final readonly class Filesystem implements FilesystemInterface
{
    public function __construct(
        private Disk $disk,
    ) {}

    #[Override]
    public function read(string $path) : string
    {
        return $this->disk->read($path);
    }

    #[Override]
    public function write(string $path, string $content, bool $append = false) : bool
    {
        return $this->disk->write($path, $content, $append);
    }

    #[Override]
    public function copy(string $source, string $destination) : bool
    {
        return $this->disk->copy($source, $destination);
    }

    #[Override]
    public function move(string $source, string $destination) : bool
    {
        return $this->disk->move($source, $destination);
    }

    #[Override]
    public function delete(string $path) : bool
    {
        return $this->disk->delete($path);
    }

    #[Override]
    public function exists(string $path) : bool
    {
        return $this->disk->exists($path);
    }

    #[Override]
    public function lastModified(string $path) : int|null
    {
        return $this->disk->lastModified($path);
    }

    #[Override]
    public function createDirectory(string $path, int $permissions = 0o755) : bool
    {
        return $this->disk->createDirectory($path, $permissions);
    }

    #[Override]
    public function deleteDirectory(string $path) : bool
    {
        return $this->disk->deleteDirectory($path);
    }

    #[Override]
    public function clearDirectory(string $path) : bool
    {
        return $this->disk->clear($path);
    }

    #[Override]
    public function listFiles(string $path) : array
    {
        return $this->disk->listFiles($path);
    }

    #[Override]
    public function isWritable(string $path) : bool
    {
        return $this->disk->isWritable($path);
    }

    #[Override]
    public function setPermissions(string $path, int $permissions) : bool
    {
        return $this->disk->setPermissions($path, $permissions);
    }
}
