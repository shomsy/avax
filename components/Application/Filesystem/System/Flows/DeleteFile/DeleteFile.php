<?php

declare(strict_types=1);

namespace Avax\Components\Application\Filesystem\System\Flows\DeleteFile;

use RuntimeException;

final readonly class DeleteFile
{
    public function execute(string $path) : bool
    {
        $cleanPath = $this->sanitizePath($path);

        if (! file_exists($cleanPath)) {
            return true;
        }

        if (! is_file($cleanPath)) {
            throw new RuntimeException("Not a file: {$path}");
        }

        unlink($cleanPath);

        return true;
    }

    private function sanitizePath(string $path) : string
    {
        return str_replace(["\0", "\n", "\r"], '', $path);
    }
}