<?php

declare(strict_types=1);

namespace Avax\Components\Application\Filesystem\Disks;

use Avax\Components\Application\Filesystem\Configuration\FilesystemConfig;
use Avax\Components\Application\Filesystem\Disks\Local\LocalDisk;

final readonly class ResolveDisk
{
    public function __construct(
        private FilesystemConfig $filesystemConfig = new FilesystemConfig(default: 'local', disks: ['local' => ['driver' => 'local']]),
    ) {}

    public function execute(string|null $name = null) : Disk
    {
        $name       ??= $this->filesystemConfig->default;
        $diskConfig = $this->filesystemConfig->disk(name: $name)
            ?? throw new UnsupportedDiskDriver(driver: $name);
        $driver     = $diskConfig['driver'] ?? $name;

        return match ($driver) {
            'local' => new LocalDisk(),
            default => throw new UnsupportedDiskDriver(driver: $driver),
        };
    }
}
