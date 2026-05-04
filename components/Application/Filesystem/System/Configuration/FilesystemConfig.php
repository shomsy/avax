<?php

declare(strict_types=1);

namespace Avax\Components\Application\Filesystem\System\Configuration;

readonly class FilesystemConfig
{
    public function __construct(
        public string $default = 'local',
        public array $disks = [],
    ) {
    }

    public static function defaults(): self
    {
        return new self(
            default: 'local',
            disks  : [
                         'local' => [
                             'driver' => 'local',
                         ],
                     ],
        );
    }

    public function disk(string $name): ?array
    {
        return $this->disks[$name] ?? null;
    }
}
