<?php

declare(strict_types=1);

namespace Avax\Components\Application\Filesystem\System\Flows\ListDirectory;

use Avax\Components\Application\Filesystem\System\Foundation\Failure\DirectoryNotFound;
use Avax\Components\Application\Filesystem\System\Foundation\Values\DirectoryListing;

final readonly class ListDirectory
{
    /**
     * @return list<string>
     */
    public function execute(string $path) : array
    {
        $cleanPath = $this->sanitizePath($path);

        if (! is_dir($cleanPath)) {
            throw new DirectoryNotFound($path);
        }

        $items = scandir($cleanPath);
        if ($items === false) {
            return [];
        }

        $result = [];
        foreach ($items as $item) {
            if ($item !== '.' && $item !== '..') {
                $result[] = $item;
            }
        }

        sort($result);

        return $result;
    }

    private function sanitizePath(string $path) : string
    {
        return str_replace(["\0", "\n", "\r"], '', $path);
    }
}