<?php

declare(strict_types=1);

namespace Avax\Components\Application\Storage\System\Capabilities\Disks;

use Avax\Components\Application\Storage\System\Foundation\Failure\DiskNotFound;

final readonly class ResolveDisk
{
    public function __construct(
        private RegisteredDisks $registry,
    ) {}

    /**
 * @throws DiskNotFound
 */
public function execute(string $name) : Disk
    {
        $disk = $this->registry->get($name);

        if ($disk === null) {
            throw new DiskNotFound($name);
        }

        return $disk;
    }
}