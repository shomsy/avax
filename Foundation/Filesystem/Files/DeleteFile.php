<?php

declare(strict_types=1);

namespace Avax\Filesystem\Files;

use Avax\Filesystem\Disks\Disk;

final class DeleteFile
{
    public function __construct(private readonly Disk $disk) {}

    public function execute(string $path) : bool
    {
        return $this->disk->delete(path: $path);
    }
}
