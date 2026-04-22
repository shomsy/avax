<?php

declare(strict_types=1);

namespace Avax\Filesystem\Files;

use Avax\Filesystem\Disks\Disk;

class ReadFileLastModifiedAt
{
    private Disk $disk;

    public function __construct(Disk $disk) { $this->disk = $disk; }

    public function execute(string $path) : int|null
    {
        if (! file_exists(filename: $path)) {
            return null;
        }

        $mtime = filemtime(filename: $path);

        return $mtime !== false ? $mtime : null;
    }
}