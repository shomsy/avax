<?php

declare(strict_types=1);

namespace Avax\Components\Application\Filesystem\Directories;

use Avax\Components\Application\Filesystem\Disks\Disk;

final class ClearDirectory
{
    public function __construct(private readonly Disk $disk) {}

    public function execute(string $path) : bool
    {
        return $this->disk->clear(path: $path);
    }
}
