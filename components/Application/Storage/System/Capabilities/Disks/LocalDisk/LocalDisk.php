<?php

declare(strict_types=1);

namespace Avax\Components\Application\Storage\System\Capabilities\Disks\LocalDisk;

use Avax\Components\Application\Filesystem\System\PublicSurface\Filesystem;
use Avax\Components\Application\Storage\System\Capabilities\Disks\Disk;
use Avax\Components\Application\Storage\System\Foundation\Failure\StoredObjectNotFound;
use Avax\Components\Application\Storage\System\Foundation\Failure\TemporaryUrlNotSupported;
use Avax\Components\Application\Storage\System\Foundation\Values\StoragePath;
use DateTimeInterface;

final readonly class LocalDisk implements Disk
{
    public function __construct(
        private Filesystem $filesystem,
        private string     $root = '',
    ) {}

    /**
 * @throws StoredObjectNotFound
 */
public function read(StoragePath $path) : string
    {
        $fullPath = $this->resolvePath($path);

        if (! $this->filesystem->exists($fullPath)) {
            throw new StoredObjectNotFound($path->path);
        }

        return $this->filesystem->read($fullPath);
    }

    private function resolvePath(StoragePath $path) : string
    {
        if ($this->root === '') {
            return $path->path;
        }

        $cleanRoot = rtrim($this->root, '/');
        $cleanPath = ltrim($path->path, '/');

        return $cleanRoot . '/' . $cleanPath;
    }

    public function exists(StoragePath $path) : bool
    {
        $fullPath = $this->resolvePath($path);

        return $this->filesystem->exists($fullPath);
    }

    public function write(StoragePath $path, string $content) : bool
    {
        $fullPath = $this->resolvePath($path);

        return $this->filesystem->write($fullPath, $content);
    }

    public function delete(StoragePath $path) : bool
    {
        $fullPath = $this->resolvePath($path);

        return $this->filesystem->delete($fullPath);
    }

    public function copy(StoragePath $source, StoragePath $destination) : bool
    {
        $fullSource = $this->resolvePath($source);
        $fullDest   = $this->resolvePath($destination);

        return $this->filesystem->copy($fullSource, $fullDest);
    }

    public function move(StoragePath $source, StoragePath $destination) : bool
    {
        $fullSource = $this->resolvePath($source);
        $fullDest   = $this->resolvePath($destination);

        return $this->filesystem->move($fullSource, $fullDest);
    }

    public function url(StoragePath $path) : string
    {
        return 'file://' . $this->resolvePath($path);
    }

    /**
 * @throws TemporaryUrlNotSupported
 */
public function temporaryUrl(StoragePath $path, DateTimeInterface $expires) : string
    {
        throw new TemporaryUrlNotSupported('local');
    }

    public function supportsTemporaryUrl() : bool
    {
        return false;
    }
}