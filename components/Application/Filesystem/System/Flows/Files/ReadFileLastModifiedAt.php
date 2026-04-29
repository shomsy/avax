<?php

declare(strict_types=1);

namespace Avax\Components\Application\Filesystem\Files;

use Avax\Components\Application\Filesystem\Disks\Disk;

final class ReadFileLastModifiedAt
{
    public function __construct(private readonly Disk $disk) {}

    public function execute(string $path) : int|null
    {
        return $this->disk->lastModified(path: $path);
    }
}
