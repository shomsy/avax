<?php

declare(strict_types=1);

namespace components\Filesystem\Disks;

use components\Filesystem\Configuration\FilesystemConfig;
use components\Filesystem\Disks\Local\LocalDisk;

final readonly class ResolveDisk
{
    public function __construct(
        private FilesystemConfig $config = new FilesystemConfig(default: 'local', disks: ['local' => ['driver' => 'local']]),
    ) {}

    public function execute(string|null $name = null) : Disk
    {
        $name       ??= $this->config->default;
        $diskConfig = $this->config->disk(name: $name)
            ?? throw new UnsupportedDiskDriver(driver: $name);
        $driver     = $diskConfig['driver'] ?? $name;

        return match ($driver) {
            'local' => new LocalDisk(),
            default => throw new UnsupportedDiskDriver(driver: $driver),
        };
    }
}
