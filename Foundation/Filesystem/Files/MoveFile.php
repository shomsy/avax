<?php

declare(strict_types=1);

namespace Avax\Filesystem\Files;

use Avax\Filesystem\Disks\Disk;

class MoveFile
{
    private Disk $disk;

    public function __construct(Disk $disk) { $this->disk = $disk; }

    public function execute(string $source, string $destination) : bool
    {
        if (! file_exists(filename: $source)) {
            throw new FileNotFound(path: $source);
        }

        $directory = dirname(path: $destination);
        if (! is_dir(filename: $directory)) {
            mkdir(directory: $directory, permissions: 0755, recursive: true);
        }

        if (! rename(from: $source, to: $destination)) {
            throw new FileMoveFailed(path: $destination);
        }

        return true;
    }
}