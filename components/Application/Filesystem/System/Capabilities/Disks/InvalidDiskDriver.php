<?php

declare(strict_types=1);

namespace Avax\Components\Application\Filesystem\System\Capabilities\Disks;

use RuntimeException;

class InvalidDiskDriver extends RuntimeException
{
    public function __construct(string $driver)
    {
        parent::__construct(message: 'Unsupported disk driver: '.$driver);
    }
}
