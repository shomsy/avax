<?php

declare(strict_types=1);

namespace Avax\Components\Application\Storage\System\Flows\GenerateStoredObjectUrl;

use Avax\Components\Application\Storage\System\Capabilities\Disks\Disk;
use Avax\Components\Application\Storage\System\Foundation\Values\StoragePath;

final readonly class GenerateStoredObjectUrl
{
    public function __construct(
        private Disk $disk,
    ) {}

    public function execute(string $path) : string
    {
        return $this->disk->url(new StoragePath($path));
    }
}