<?php

declare(strict_types=1);

namespace Avax\Components\Operations\Filesystem\System\Flows\WriteFile;

use Avax\Components\Operations\Filesystem\System\Foundation\Failure\FilesystemException;

final readonly class WriteFile
{
    public function write(string $path, string $content) : bool
    {
        $directory = dirname($path);

        if (! is_dir($directory)) {
            mkdir($directory, 0755, true);
        }

        $result = @file_put_contents($path, $content);

        if ($result === false) {
            throw new FilesystemException("Unable to write file: {$path}");
        }

        return true;
    }
}
