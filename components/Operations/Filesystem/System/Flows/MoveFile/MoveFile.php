<?php

declare(strict_types=1);

namespace Avax\Components\Operations\Filesystem\System\Flows\MoveFile;

use Avax\Components\Operations\Filesystem\System\Foundation\Failure\FilesystemException;

final readonly class MoveFile
{
    public function move(string $from, string $to) : bool
    {
        $directory = dirname($to);

        if (! is_dir($directory)) {
            mkdir($directory, 0755, true);
        }

        $result = @rename($from, $to);

        if (! $result) {
            throw new FilesystemException("Unable to move from {$from} to {$to}");
        }

        return true;
    }
}
