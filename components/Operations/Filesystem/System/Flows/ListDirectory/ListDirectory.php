<?php

declare(strict_types=1);

namespace Avax\Components\Operations\Filesystem\System\Flows\ListDirectory;

use Avax\Components\Operations\Filesystem\System\Foundation\Failure\FilesystemException;

final readonly class ListDirectory
{
    /**
     * @return list<string>
     */
    public function list(string $path) : array
    {
        if (! is_dir($path)) {
            throw new FilesystemException("Not a directory: {$path}");
        }

        $items = scandir($path);

        if ($items === false) {
            throw new FilesystemException("Unable to list directory: {$path}");
        }

        return array_values(array_filter($items, fn (string $item) : bool => $item !== '.' && $item !== '..'));
    }
}
