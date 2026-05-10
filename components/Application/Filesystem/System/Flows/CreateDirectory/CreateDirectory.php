<?php

declare(strict_types=1);

namespace Avax\Components\Application\Filesystem\System\Flows\CreateDirectory;

use Avax\Components\Application\Filesystem\System\Foundation\Failure\FilesystemOperationFailed;

final readonly class CreateDirectory
{
    public function execute(string $path, int $permissions = 0o755) : bool
    {
        $cleanPath = $this->sanitizePath($path);

        if (is_dir($cleanPath)) {
            return true;
        }

        $result = mkdir($cleanPath, $permissions, recursive: true);

        if (! $result && ! is_dir($cleanPath)) {
            throw new FilesystemOperationFailed('create directory', $path);
        }

        return true;
    }

    private function sanitizePath(string $path) : string
    {
        return str_replace(["\0", "\n", "\r"], '', $path);
    }
}