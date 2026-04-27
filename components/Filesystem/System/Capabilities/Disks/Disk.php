<?php

declare(strict_types=1);

namespace Avax\Components\Filesystem\System\Capabilities\Disks;

final class Disk
{
    public function __construct(
        public readonly string $name,
    ) {
    }

    public function path(string $path): string
    {
        return $path;
    }
}