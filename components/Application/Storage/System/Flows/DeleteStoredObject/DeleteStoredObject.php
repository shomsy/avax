<?php

declare(strict_types=1);

namespace Avax\Components\Application\Storage\System\Flows\DeleteStoredObject;

use Avax\Components\Application\Storage\System\Capabilities\Disks\Disk;
use Avax\Components\Application\Storage\System\Foundation\Values\StoragePath;

final readonly class DeleteStoredObject
{
    public function __construct(
        private Disk $disk,
    ) {}

    public function execute(string $path) : bool
    {
        return $this->disk->delete(new StoragePath($path));
    }
}