<?php

declare(strict_types=1);

namespace Avax\Filesystem\Directories;

use Avax\Filesystem\Disks\Disk;

class CreateDirectory
{
    private Disk $disk;

    public function __construct(Disk $disk) { $this->disk = $disk; }

    public function execute(string $path, int $permissions = 0755) : bool
    {
        if (is_dir(filename: $path)) {
            return true;
        }

        if (! mkdir(directory: $path, permissions: $permissions, recursive: true)) {
            throw new DirectoryCreateFailed(path: $path);
        }

        return true;
    }
}