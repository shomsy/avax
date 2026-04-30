<?php

declare(strict_types=1);

namespace Avax\Components\Application\Filesystem\Directories;

use Avax\Components\Application\Filesystem\Disks\Disk;

final class EnsureDirectoryIsWritable
{
    private const int RETRY_ATTEMPTS = 3;
    private const int RETRY_DELAY    = 100000;

    public function __construct(private readonly Disk $disk) {}

    public function execute(string $path) : bool
    {
        new EnsureDirectoryExists(disk: $this->disk)->execute(path: $path);

        if ($this->disk->isWritable(path: $path)) {
            return true;
        }

        if (! $this->disk->setPermissions(path: $path, permissions: 0o755)) {
            return false;
        }

        for ($attempt = 1; $attempt <= self::RETRY_ATTEMPTS; $attempt++) {
            if ($this->disk->isWritable(path: $path)) {
                return true;
            }

            usleep(microseconds: self::RETRY_DELAY);
        }

        return false;
    }
}
