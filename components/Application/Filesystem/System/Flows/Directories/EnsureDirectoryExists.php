<?php

declare(strict_types=1);

namespace Avax\Components\Application\Filesystem\Directories;

use Avax\Components\Application\Filesystem\Disks\Disk;

final class EnsureDirectoryExists
{
    public function __construct(private readonly Disk $disk) {}

    public function execute(string $path) : bool
    {
        return $this->disk->createDirectory(path: $path, permissions: 0755);
    }
}
