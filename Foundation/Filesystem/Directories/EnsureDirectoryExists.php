<?php

declare(strict_types=1);

namespace Avax\Filesystem\Directories;

use Avax\Filesystem\Disks\Disk;

class EnsureDirectoryExists
{
    private Disk $disk;

    public function __construct(Disk $disk) { $this->disk = $disk; }

    public function execute(string $path) : bool
    {
        if (is_dir(filename: $path)) {
            return true;
        }

        if (! mkdir(directory: $path, permissions: 0755, recursive: true)) {
            throw new DirectoryCreateFailed(path: $path);
        }

        return true;
    }
}