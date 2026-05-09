<?php

declare(strict_types=1);

namespace Avax\Components\Application\Storage\System\Configuration;

use Avax\Components\Application\Filesystem\System\PublicSurface\Filesystem;
use Avax\Components\Application\Storage\System\Capabilities\Disks\LocalDisk\CreateLocalDisk;
use Avax\Components\Application\Storage\System\Capabilities\Disks\RegisteredDisks;
use Avax\Components\Application\Storage\System\Foundation\Values\DiskName;

final readonly class RegisterStorageDisks
{
    public function __construct(
        private RegisteredDisks $registry,
        private CreateLocalDisk $createLocalDisk,
    ) {}

    /**
     * @param array<string, array<string, mixed>> $disks
     */
    public function execute(array $disks) : void
    {
        foreach ($disks as $name => $diskConfig) {
            $driver = $diskConfig['driver'] ?? 'local';

            if ($driver === 'local') {
                $root     = $diskConfig['root'] ?? '';
                $diskName = new DiskName($name);
                $disk     = $this->createLocalDisk->execute($diskName, $root);
                $this->registry->register($diskName, $disk);
            }
        }
    }
}