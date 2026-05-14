<?php

declare(strict_types=1);

namespace Avax\Components\Application\Storage\System\Configuration;

final readonly class StorageConfiguration
{
    /**
     * @param array<string, array{driver: string, root?: string}> $disks
     */
    public function __construct(
        public string $defaultDisk = 'local',
        public array  $disks = [],
    ) {}

    /**
     * @param array<string, mixed> $config
     */
    public static function fromArray(array $config) : self
    {
        return new self(
            defaultDisk: $config['default'] ?? 'local',
            disks      : $config['disks'] ?? [],
        );
    }

    /**
     * @return array<string, mixed>
     */
    public function toArray() : array
    {
        return [
            'default' => $this->defaultDisk,
            'disks'   => $this->disks,
        ];
    }

    /**
     * @return array{driver: string, root?: string}|null
     */
    public function getDiskConfig(string $name) : array|null
    {
        return $this->disks[$name] ?? null;
    }
}