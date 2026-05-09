<?php

declare(strict_types=1);

namespace Avax\Components\Application\Filesystem\Configuration;

use Avax\Components\Application\Filesystem\System\Configuration\FilesystemConfiguration as SystemFilesystemConfiguration;

final readonly class FilesystemConfiguration
{
    public function __construct(
        public SystemFilesystemConfiguration $system,
    ) {}

    /**
     * @param array<string, mixed> $config
     */
    public static function fromArray(array $config) : self
    {
        return new self(
            system: SystemFilesystemConfiguration::fromArray($config),
        );
    }

    /**
     * @return array<string, mixed>
     */
    public function toArray() : array
    {
        return $this->system->toArray();
    }
}