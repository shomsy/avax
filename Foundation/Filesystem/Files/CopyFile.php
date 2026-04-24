<?php

declare(strict_types=1);

namespace Avax\Filesystem\Files;

use Avax\Filesystem\Disks\Disk;

final class CopyFile
{
    public function __construct(private readonly Disk $disk) {}

    public function execute(string $source, string $destination) : bool
    {
        return $this->disk->copy(source: $source, destination: $destination);
    }
}
