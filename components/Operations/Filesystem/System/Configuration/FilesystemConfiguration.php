<?php

declare(strict_types=1);

namespace Avax\Components\Operations\Filesystem\System\Configuration;

final readonly class FilesystemConfiguration
{
    public function __construct(
        public string $basePath = '',
        public int    $defaultPermissions = 0644,
        public int    $directoryPermissions = 0755,
    ) {}

    public function resolvePath(string $path) : string
    {
        if ($this->basePath === '' || str_starts_with($path, '/')) {
            return $path;
        }

        return rtrim($this->basePath, '/') . '/' . ltrim($path, '/');
    }
}
