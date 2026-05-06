<?php

declare(strict_types=1);

namespace Avax\Components\Application\Filesystem\System\Capabilities\Disks;

use Avax\Components\Application\Filesystem\System\Capabilities\Disks\Local\LocalDisk;
use Avax\Components\Application\Filesystem\System\Configuration\FilesystemConfig;

final readonly class ResolveDisk
{
    public function __construct(
        private FilesystemConfig $filesystemConfig = new FilesystemConfig(default: 'local', disks: ['local' => ['driver' => 'local']]),
    ) {
    }

    public function execute(?string $name = null): Disk
    {
        $name ??= $this->filesystemConfig->default;
        $diskConfig = $this->filesystemConfig->disk(name: $name)
            ?? throw new InvalidDiskDriver(driver: $name);
        $driver = $diskConfig['driver'] ?? $name;

        return match ($driver) {
            'local' => new LocalDisk(),
            default => throw new InvalidDiskDriver(driver: $driver),
        };
    }
}
