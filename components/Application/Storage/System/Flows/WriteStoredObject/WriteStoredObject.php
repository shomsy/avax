<?php

declare(strict_types=1);

namespace Avax\Components\Application\Storage\System\Flows\WriteStoredObject;

use Avax\Components\Application\Storage\System\Capabilities\Disks\Disk;
use Avax\Components\Application\Storage\System\Foundation\Values\StoragePath;

final readonly class WriteStoredObject
{
    public function __construct(
        private Disk $disk,
    ) {}

    public function execute(string $path, string $content) : bool
    {
        return $this->disk->write(new StoragePath($path), $content);
    }
}