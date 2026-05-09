<?php

declare(strict_types=1);

namespace Avax\Components\Application\Storage\System\Capabilities\Disks\LocalDisk;

use Avax\Components\Application\Filesystem\System\PublicSurface\Filesystem;
use Avax\Components\Application\Storage\System\Capabilities\Disks\Disk;
use Avax\Components\Application\Storage\System\Foundation\Values\DiskName;

final readonly class CreateLocalDisk
{
    public function __construct(
        private Filesystem $filesystem,
    ) {}

    public function execute(DiskName $name, string $root = '') : LocalDisk
    {
        return new LocalDisk($this->filesystem, $root);
    }
}