<?php

declare(strict_types=1);

namespace Avax\Components\Application\Storage\System\Flows\CheckStoredObject;

use Avax\Components\Application\Storage\System\Capabilities\Disks\Disk;
use Avax\Components\Application\Storage\System\Foundation\Values\StoragePath;

final readonly class CheckStoredObject
{
    public function __construct(
        private Disk $disk,
    ) {}

    public function execute(string $path) : bool
    {
        return $this->disk->exists(new StoragePath($path));
    }
}