<?php

declare(strict_types=1);

namespace Avax\Components\Application\Filesystem\System\Capabilities\Storage;

interface StorageInterface
{
    public function read(string $path) : string;

    public function write(string $path, string $content, bool $append = false) : bool;

    public function copy(string $source, string $destination) : bool;

    public function move(string $source, string $destination) : bool;

    public function delete(string $path) : bool;

    public function exists(string $path) : bool;

    public function lastModified(string $path) : ?int;

    public function createDirectory(string $path, int $permissions = 0o755) : bool;

    public function deleteDirectory(string $path) : bool;

    public function clear(string $path) : bool;

    public function isWritable(string $path) : bool;

    public function setPermissions(string $path, int $permissions) : bool;

    public function hasPermission(string $path, int $permissions) : bool;

    public function listFiles(string $path) : array;
}
