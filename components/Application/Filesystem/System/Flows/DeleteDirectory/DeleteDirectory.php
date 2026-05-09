<?php

declare(strict_types=1);

namespace Avax\Components\Application\Filesystem\System\Flows\DeleteDirectory;

use Avax\Components\Application\Filesystem\System\Foundation\Failure\DirectoryNotFound;

final readonly class DeleteDirectory
{
    public function execute(string $path) : bool
    {
        $cleanPath = $this->sanitizePath($path);

        if (! is_dir($cleanPath)) {
            throw new DirectoryNotFound($path);
        }

        if ($this->isDirectoryNotEmpty($cleanPath)) {
            return false;
        }

        rmdir($cleanPath);

        return true;
    }

    private function isDirectoryNotEmpty(string $path) : bool
    {
        $handle = opendir($path);
        if ($handle === false) {
            return false;
        }

        while ( ($entry = readdir($handle)) !== false ) {
            if ($entry !== '.' && $entry !== '..') {
                closedir($handle);

                return true;
            }
        }

        closedir($handle);

        return false;
    }

    private function sanitizePath(string $path) : string
    {
        return str_replace(["\0", "\n", "\r"], '', $path);
    }
}