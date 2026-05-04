<?php

declare(strict_types=1);

namespace Avax\Components\Application\Filesystem\System\Flows\Files;

use Avax\Components\Application\Filesystem\System\Capabilities\Disks\Disk;

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
