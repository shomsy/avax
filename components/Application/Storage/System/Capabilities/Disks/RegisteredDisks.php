<?php

declare(strict_types=1);

namespace Avax\Components\Application\Storage\System\Capabilities\Disks;

use Avax\Components\Application\Storage\System\Foundation\Values\DiskName;

final class RegisteredDisks
{
    /** @var array<string, Disk> */
    private array $disks = [];

    public function register(DiskName $name, Disk $disk) : void
    {
        $this->disks[$name->name] = $disk;
    }

    public function get(string $name) : Disk|null
    {
        return $this->disks[$name] ?? null;
    }

    /**
     * @return list<string>
     */
    public function names() : array
    {
        return array_keys($this->disks);
    }

    public function has(string $name) : bool
    {
        return isset($this->disks[$name]);
    }

    public function clear() : void
    {
        $this->disks = [];
    }
}