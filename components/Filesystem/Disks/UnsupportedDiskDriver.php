<?php

declare(strict_types=1);

namespace components\Filesystem\Disks;

use RuntimeException;

class UnsupportedDiskDriver extends RuntimeException
{
    public function __construct(string $driver)
    {
        parent::__construct(message: "Unsupported disk driver: {$driver}");
    }
}