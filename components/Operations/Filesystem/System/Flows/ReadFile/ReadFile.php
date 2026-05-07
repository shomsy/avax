<?php

declare(strict_types=1);

namespace Avax\Components\Operations\Filesystem\System\Flows\ReadFile;

use Avax\Components\Operations\Filesystem\System\Foundation\Failure\FilesystemException;

final readonly class ReadFile
{
    public function read(string $path) : string
    {
        $content = @file_get_contents($path);

        if ($content === false) {
            throw new FilesystemException("Unable to read file: {$path}");
        }

        return $content;
    }
}
