<?php

declare(strict_types=1);

namespace Avax\Components\Filesystem\System\PublicSurface;

use Avax\Components\Filesystem\System\Capabilities\Disks\Disk;

final class Filesystem implements FilesystemInterface
{
    public function disk(string $name = 'local'): Disk
    {
        return new Disk($name);
    }

    public function exists(string $path): bool
    {
        return file_exists($path);
    }

    public function read(string $path): string
    {
        return file_get_contents($path);
    }

    public function write(string $path, string $contents): void
    {
        file_put_contents($path, $contents);
    }

    public function delete(string $path): void
    {
        unlink($path);
    }

    public function mkdir(string $path, int $mode = 0755): void
    {
        mkdir($path, $mode, true);
    }
}