<?php

declare(strict_types=1);

namespace Avax\Components\Application\Filesystem\System\Flows\CopyFile;

use Avax\Components\Application\Filesystem\System\Foundation\Failure\FileNotFound;
use Avax\Components\Application\Filesystem\System\Foundation\Failure\FilesystemOperationFailed;

final readonly class CopyFile
{
    /**
 * @throws FileNotFound
 */
public function execute(string $source, string $destination) : bool
    {
        $cleanSource = $this->sanitizePath($source);
        $cleanDest   = $this->sanitizePath($destination);

        if (! file_exists($cleanSource)) {
            throw new FileNotFound($source);
        }

        $destDir = dirname($cleanDest);
        if (! is_dir($destDir)) {
            if (! mkdir($destDir, 0o755, true) && ! is_dir($destDir)) {
                throw new FilesystemOperationFailed('create directory', $destDir);
            }
        }

        $result = copy($cleanSource, $cleanDest);

        if (! $result) {
            throw new FilesystemOperationFailed('copy', "{$source} -> {$destination}");
        }

        return true;
    }

    private function sanitizePath(string $path) : string
    {
        return str_replace(["\0", "\n", "\r"], '', $path);
    }
}