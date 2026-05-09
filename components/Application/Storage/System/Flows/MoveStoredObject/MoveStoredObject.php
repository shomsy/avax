<?php

declare(strict_types=1);

namespace Avax\Components\Application\Storage\System\Flows\MoveStoredObject;

use Avax\Components\Application\Storage\System\Capabilities\Disks\Disk;
use Avax\Components\Application\Storage\System\Foundation\Values\StoragePath;

final readonly class MoveStoredObject
{
    public function __construct(
        private Disk $disk,
    ) {}

    public function execute(string $source, string $destination) : bool
    {
        return $this->disk->move(new StoragePath($source), new StoragePath($destination));
    }
}