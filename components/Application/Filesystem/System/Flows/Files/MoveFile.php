<?php

declare(strict_types=1);

namespace Avax\Components\Application\Filesystem\System\Flows\Files;

use Avax\Components\Application\Filesystem\Disks\Disk;

final readonly class MoveFile
{
    public function __construct(private Disk $disk) {}

    public function execute(string $source, string $destination) : bool
    {
        return $this->disk->move(source: $source, destination: $destination);
    }
}
