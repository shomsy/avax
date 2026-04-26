<?php

declare(strict_types=1);

namespace Avax\Filesystem;

use Avax\Filesystem\Disks\Disk;

/**
 * Core contract for all filesystem operations.
 * Implementations may target local disk, cloud storage, or in-memory adapters.
 */
interface Filesystem
{
    public function get(string $path) : string;

    public function put(string $path, string $content) : void;

    public function append(string $path, string $content) : void;

    public function copy(string $source, string $destination) : void;

    public function move(string $source, string $destination) : void;

    public function exists(string $path) : bool;

    public function delete(string $path) : void;

    public function lastModified(string $path) : int|null;

    public function ensureDirectory(string $path) : void;

    public function ensureDirectoryIsWritable(string $path) : bool;

    public function createDirectory(string $path, int $permissions = 0755) : void;

    public function deleteDirectory(string $path) : void;

    public function clearDirectory(string $path) : void;

    public function listFiles(string $path) : array;

    public function isWritable(string $path) : bool;

    public function setPermissions(string $path, int $permissions) : bool;

    public function hasPermission(string $path, int $permissions) : bool;

    public function disk(string|null $name = null) : Disk;
}
