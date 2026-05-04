<?php

declare(strict_types=1);

namespace Avax\Components\Application\Filesystem\System\Flows\Files;

use Avax\Components\Application\Filesystem\Disks\Disk;

final readonly class ReadFileLastModifiedAt
{
    public function __construct(private Disk $disk) {}

    public function execute(string $path) : ?int
    {
        return $this->disk->lastModified(path: $path);
    }
}
