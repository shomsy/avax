<?php

declare(strict_types=1);

namespace Avax\Components\Application\Storage\System\Flows\ReadStoredObject;

use Avax\Components\Application\Storage\System\Capabilities\Disks\Disk;
use Avax\Components\Application\Storage\System\Foundation\Values\StoragePath;

final readonly class ReadStoredObject
{
    public function __construct(
        private Disk $disk,
    ) {}

    public function execute(string $path) : string
    {
        return $this->disk->read(new StoragePath($path));
    }
}