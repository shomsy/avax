<?php

declare(strict_types=1);

namespace Avax\Components\Application\Filesystem\Files;

use Avax\Components\Application\Filesystem\Disks\Disk;

final class MoveFile
{
    public function __construct(private readonly Disk $disk) {}

    public function execute(string $source, string $destination) : bool
    {
        return $this->disk->move(source: $source, destination: $destination);
    }
}
