<?php

declare(strict_types=1);

namespace Avax\Filesystem\Disks;

use Avax\Filesystem\Disks\Local\LocalDisk;

class ResolveDisk
{
    public function execute(string|null $name = null) : Disk
    {
        $driver = 'local';

        return match ($driver) {
            'local' => new LocalDisk(),
            default => throw new UnsupportedDiskDriver(driver: $driver),
        };
    }
}