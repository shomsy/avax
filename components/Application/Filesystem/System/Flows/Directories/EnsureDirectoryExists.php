<?php

declare(strict_types=1);

namespace Avax\Components\Application\Filesystem\System\Flows\Directories;

use Avax\Components\Application\Filesystem\Disks\Disk;

final readonly class EnsureDirectoryExists
{
    public function __construct(private Disk $disk)
    {
    }

    public function execute(string $path): bool
    {
        return $this->disk->createDirectory(path: $path, permissions: 0o755);
    }
}
