<?php

declare(strict_types=1);

namespace Avax\Components\Application\Filesystem\System\Flows\AppendToFile;

use Avax\Components\Application\Filesystem\System\Foundation\Failure\FilesystemOperationFailed;

final readonly class AppendToFile
{
    public function execute(string $path, string $content) : bool
    {
        $cleanPath = $this->sanitizePath($path);
        $directory = dirname($cleanPath);

        if (! is_dir($directory)) {
            if (! mkdir($directory, 0o755, true) && ! is_dir($directory)) {
                throw new FilesystemOperationFailed('create directory', $directory);
            }
        }

        $result = file_put_contents($cleanPath, $content, FILE_APPEND | LOCK_EX);

        if ($result === false) {
            throw new FilesystemOperationFailed('append', $path);
        }

        return true;
    }

    private function sanitizePath(string $path) : string
    {
        return str_replace(["\0", "\n", "\r"], '', $path);
    }
}