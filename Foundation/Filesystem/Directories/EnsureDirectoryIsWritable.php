<?php

declare(strict_types=1);

namespace Avax\Filesystem\Directories;

use Avax\Filesystem\Disks\Disk;

class EnsureDirectoryIsWritable
{
    private const int RETRY_ATTEMPTS = 3;
    private const int RETRY_DELAY = 100000;

    private Disk $disk;

    public function __construct(Disk $disk) { $this->disk = $disk; }

    public function execute(string $path) : bool
    {
        if (! is_dir(filename: $path)) {
            throw new DirectoryCreateFailed(path: $path);
        }

        if (is_writable(filename: $path)) {
            return true;
        }

        if (! chmod(filename: $path, permissions: 0755)) {
            return false;
        }

        for ($attempt = 1; $attempt <= self::RETRY_ATTEMPTS; $attempt++) {
            if (is_writable(filename: $path)) {
                return true;
            }
            usleep(microseconds: self::RETRY_DELAY);
        }

        return false;
    }
}