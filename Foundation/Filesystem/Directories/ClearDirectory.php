<?php

declare(strict_types=1);

namespace Avax\Filesystem\Directories;

use Avax\Filesystem\Disks\Disk;
use FilesystemIterator;

class ClearDirectory
{
    private Disk $disk;

    public function __construct(Disk $disk) { $this->disk = $disk; }

    public function execute(string $path) : bool
    {
        if (! is_dir(filename: $path)) {
            throw new DirectoryClearFailed(path: $path);
        }

        foreach (new FilesystemIterator(directory: $path, flags: FilesystemIterator::SKIP_DOTS) as $item) {
            $itemPath = $item->getPathname();

            if ($item->isDir()) {
                (new DeleteDirectory(disk: $this->disk))->execute(path: $itemPath);
            } else {
                unlink(filename: $itemPath);
            }
        }

        return true;
    }
}