<?php

declare(strict_types=1);

namespace Avax\Filesystem\Files;

use Avax\Filesystem\Disks\Disk;

class DeleteFile
{
    private Disk $disk;

    public function __construct(Disk $disk) { $this->disk = $disk; }

    public function execute(string $path) : bool
    {
        if (! file_exists(filename: $path)) {
            return true;
        }

        if (! unlink(filename: $path)) {
            throw new FileDeleteFailed(path: $path);
        }

        return true;
    }
}