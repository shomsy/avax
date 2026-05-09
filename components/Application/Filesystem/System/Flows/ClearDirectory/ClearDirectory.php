<?php

declare(strict_types=1);

namespace Avax\Components\Application\Filesystem\System\Flows\ClearDirectory;

use Avax\Components\Application\Filesystem\System\Foundation\Failure\DirectoryNotFound;

final readonly class ClearDirectory
{
    public function execute(string $path) : bool
    {
        $cleanPath = $this->sanitizePath($path);

        if (! is_dir($cleanPath)) {
            throw new DirectoryNotFound($path);
        }

        $items = scandir($cleanPath);
        if ($items === false) {
            return false;
        }

        foreach ($items as $item) {
            if ($item === '.' || $item === '..') {
                continue;
            }

            $itemPath = $cleanPath . '/' . $item;

            if (is_dir($itemPath)) {
                $this->removeDirectoryRecursive($itemPath);
            } else {
                unlink($itemPath);
            }
        }

        return true;
    }

    private function sanitizePath(string $path) : string
    {
        return str_replace(["\0", "\n", "\r"], '', $path);
    }

    private function removeDirectoryRecursive(string $path) : void
    {
        $items = scandir($path);
        if ($items === false) {
            return;
        }

        foreach ($items as $item) {
            if ($item === '.' || $item === '..') {
                continue;
            }

            $itemPath = $path . '/' . $item;

            if (is_dir($itemPath)) {
                $this->removeDirectoryRecursive($itemPath);
            } else {
                unlink($itemPath);
            }
        }

        rmdir($path);
    }
}