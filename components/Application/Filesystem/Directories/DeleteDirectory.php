<?php

declare(strict_types=1);

namespace Avax\Components\Application\Filesystem\Directories;

use Avax\Components\Application\Filesystem\Disks\Disk;

final class DeleteDirectory
{
    public function __construct(private readonly Disk $disk) {}

    public function execute(string $path) : bool
    {
        return $this->disk->deleteDirectory(path: $path);
    }
}
