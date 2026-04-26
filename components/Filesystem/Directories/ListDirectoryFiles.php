<?php

declare(strict_types=1);

namespace Avax\Filesystem\Directories;

use Avax\Filesystem\Disks\Disk;

final class ListDirectoryFiles
{
    public function __construct(private readonly Disk $disk) {}

    public function execute(string $path) : array
    {
        return $this->disk->listFiles(path: $path);
    }
}
