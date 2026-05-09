<?php

declare(strict_types=1);

namespace Avax\Components\Application\Storage\System\Capabilities\Disks\MemoryDisk;

use Avax\Components\Application\Storage\System\Capabilities\Disks\Disk;
use Avax\Components\Application\Storage\System\Foundation\Failure\StoredObjectNotFound;
use Avax\Components\Application\Storage\System\Foundation\Failure\TemporaryUrlNotSupported;
use Avax\Components\Application\Storage\System\Foundation\Values\StoragePath;
use DateTimeInterface;

/**
 * In-memory disk implementation for testing.
 *
 * Stores file contents in a plain array. No real filesystem I/O.
 * Supports all Disk operations except temporary URLs.
 */
final class MemoryDisk implements Disk
{
    /** @var array<string, string> */
    private array $files = [];

    public function read(StoragePath $path) : string
    {
        $key = $this->key($path);

        if (!isset($this->files[$key])) {
            throw new StoredObjectNotFound($path->path);
        }

        return $this->files[$key];
    }

    public function write(StoragePath $path, string $content) : bool
    {
        $this->files[$this->key($path)] = $content;

        return true;
    }

    public function delete(StoragePath $path) : bool
    {
        $key = $this->key($path);

        if (!isset($this->files[$key])) {
            return false;
        }

        unset($this->files[$key]);

        return true;
    }

    public function exists(StoragePath $path) : bool
    {
        return isset($this->files[$this->key($path)]);
    }

    public function copy(StoragePath $source, StoragePath $destination) : bool
    {
        $srcKey = $this->key($source);

        if (!isset($this->files[$srcKey])) {
            return false;
        }

        $this->files[$this->key($destination)] = $this->files[$srcKey];

        return true;
    }

    public function move(StoragePath $source, StoragePath $destination) : bool
    {
        $this->copy($source, $destination);

        return $this->delete($source);
    }

    public function url(StoragePath $path) : string
    {
        return 'memory://' . $this->key($path);
    }

    public function temporaryUrl(StoragePath $path, DateTimeInterface $expires) : string
    {
        throw new TemporaryUrlNotSupported('memory');
    }

    public function supportsTemporaryUrl() : bool
    {
        return false;
    }

    /**
     * Clear all stored files.
     */
    public function clear() : void
    {
        $this->files = [];
    }

    /**
     * @return list<string>
     */
    public function paths() : array
    {
        return array_keys($this->files);
    }

    private function key(StoragePath $path) : string
    {
        return ltrim($path->path, '/');
    }
}
