<?php

declare(strict_types=1);

namespace Avax\Components\Operations\Filesystem\System\Flows\CopyFile;

use Avax\Components\Operations\Filesystem\System\Foundation\Failure\FilesystemException;

final readonly class CopyFile
{
    public function copy(string $from, string $to) : bool
    {
        $directory = dirname($to);

        if (! is_dir($directory)) {
            mkdir($directory, 0755, true);
        }

        $result = @copy($from, $to);

        if (! $result) {
            throw new FilesystemException("Unable to copy from {$from} to {$to}");
        }

        return true;
    }
}
