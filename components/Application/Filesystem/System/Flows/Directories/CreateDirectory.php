<?php

declare(strict_types=1);

namespace Avax\Components\Application\Filesystem\Directories;

use Avax\Components\Application\Filesystem\Disks\Disk;

final readonly class CreateDirectory
{
    public function __construct(private Disk $disk)
    {
    }

    public function execute(string $path, int $permissions = 0o755): bool
    {
        return $this->disk->createDirectory(path: $path, permissions: $permissions);
    }
}
