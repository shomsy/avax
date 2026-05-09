<?php

declare(strict_types=1);

namespace Avax\Components\Application\Storage\System\Capabilities\Disks;

use Avax\Components\Application\Storage\System\Foundation\Values\DiskName;

final readonly class RegisterDisk
{
    public function __construct(
        private RegisteredDisks $registry,
    ) {}

    public function execute(DiskName $name, Disk $disk) : void
    {
        $this->registry->register($name, $disk);
    }
}