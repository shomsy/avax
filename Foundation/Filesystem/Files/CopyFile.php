<?php

declare(strict_types=1);

namespace Avax\Filesystem\Files;

use Avax\Filesystem\Disks\Disk;

class CopyFile
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

        if (! copy(from: $source, to: $destination)) {
            throw new FileCopyFailed(path: $destination);
        }

        return true;
    }
}