<?php

declare(strict_types=1);

namespace Avax\Components\Application\Filesystem\Files;

use Avax\Components\Application\Filesystem\Disks\Disk;

final readonly class DeleteFile
{
    public function __construct(private Disk $disk)
    {
    }

    public function execute(string $path): bool
    {
        return $this->disk->delete(path: $path);
    }
}
