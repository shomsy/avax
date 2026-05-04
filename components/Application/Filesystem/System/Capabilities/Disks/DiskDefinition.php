<?php

declare(strict_types=1);

namespace Avax\Components\Application\Filesystem\System\Capabilities\Disks;

readonly class DiskDefinition
{
    public function __construct(
        public string $name,
        public string $driver,
        public array $config = [],
    ) {}
}
