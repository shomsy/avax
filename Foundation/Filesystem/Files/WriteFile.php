<?php

declare(strict_types=1);

namespace Avax\Filesystem\Files;

use Avax\Filesystem\Disks\Disk;

class WriteFile
{
    private Disk $disk;

    public function __construct(Disk $disk) { $this->disk = $disk; }

    public function execute(string $path, string $content) : bool
    {
        $directory = dirname(path: $path);
        if (! is_dir(filename: $directory)) {
            mkdir(directory: $directory, permissions: 0755, recursive: true);
        }

        if (file_put_contents(filename: $path, data: $content . PHP_EOL) === false) {
            throw new FileWriteFailed(path: $path);
        }

        return true;
    }
}