<?php

declare(strict_types=1);

namespace Avax\Filesystem\Files;

use Avax\Filesystem\Disks\Disk;

class ReadFile
{
    private Disk $disk;

    public function __construct(Disk $disk) { $this->disk = $disk; }

    public function execute(string $path) : string
    {
        if (! file_exists(filename: $path)) {
            throw new FileNotFound(path: $path);
        }

        if (! is_readable(filename: $path)) {
            throw new FileNotFound(path: $path);
        }

        $content = file_get_contents(filename: $path);
        if ($content === false) {
            throw new FileNotFound(path: $path);
        }

        return $content;
    }
}