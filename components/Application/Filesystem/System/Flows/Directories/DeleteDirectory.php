<?php

declare(strict_types=1);

namespace Avax\Components\Application\Filesystem\System\Flows\Directories;

use Avax\Components\Application\Filesystem\System\Capabilities\Disks\Disk;

final readonly class DeleteDirectory
{
    public function __construct(private Disk $disk)
    {
    }

    public function execute(string $path): bool
    {
        return $this->disk->deleteDirectory(path: $path);
    }
}
