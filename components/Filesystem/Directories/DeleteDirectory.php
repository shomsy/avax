<?php

declare(strict_types=1);

namespace Avax\Filesystem\Directories;

use Avax\Filesystem\Disks\Disk;

final class DeleteDirectory
{
    public function __construct(private readonly Disk $disk) {}

    public function execute(string $path) : bool
    {
        return $this->disk->deleteDirectory(path: $path);
    }
}
