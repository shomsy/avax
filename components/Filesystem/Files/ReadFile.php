<?php

declare(strict_types=1);

namespace Avax\Filesystem\Files;

use Avax\Filesystem\Disks\Disk;

final class ReadFile
{
    public function __construct(private readonly Disk $disk) {}

    public function execute(string $path) : string
    {
        return $this->disk->read(path: $path);
    }
}
