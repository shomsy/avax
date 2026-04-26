<?php

declare(strict_types=1);

namespace components\Filesystem\Directories;

use components\Filesystem\Disks\Disk;

final class CreateDirectory
{
    public function __construct(private readonly Disk $disk) {}

    public function execute(string $path, int $permissions = 0755) : bool
    {
        return $this->disk->createDirectory(path: $path, permissions: $permissions);
    }
}
