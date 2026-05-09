<?php

declare(strict_types=1);

namespace Avax\Components\Application\Filesystem\System\Configuration;

final readonly class FilesystemConfiguration
{
    public function __construct(
        public string $root = '',
        public int    $defaultPermissions = 0o755,
        public int    $defaultFilePermissions = 0o644,
        public bool   $strictMode = true,
    ) {}

    /**
     * @param array<string, mixed> $config
     */
    public static function fromArray(array $config) : self
    {
        return new self(
            root                  : $config['root'] ?? '',
            defaultPermissions    : $config['default_permissions'] ?? 0o755,
            defaultFilePermissions: $config['default_file_permissions'] ?? 0o644,
            strictMode            : $config['strict_mode'] ?? true,
        );
    }

    /**
     * @return array<string, mixed>
     */
    public function toArray() : array
    {
        return [
            'root'                     => $this->root,
            'default_permissions'      => $this->defaultPermissions,
            'default_file_permissions' => $this->defaultFilePermissions,
            'strict_mode'              => $this->strictMode,
        ];
    }
}