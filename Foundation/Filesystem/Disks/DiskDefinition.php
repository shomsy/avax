<?php

declare(strict_types=1);

namespace Avax\Filesystem\Disks;

readonly class DiskDefinition
{
    public function __construct(
        public string $name,
        public string $driver,
        public array $config = []
    ) {}
}