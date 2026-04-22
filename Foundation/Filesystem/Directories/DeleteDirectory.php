<?php

declare(strict_types=1);

namespace Avax\Filesystem\Directories;

use Avax\Filesystem\Disks\Disk;
use FilesystemIterator;

class DeleteDirectory
{
    private Disk $disk;

    public function __construct(Disk $disk) { $this->disk = $disk; }

    public function execute(string $path) : bool
    {
        if (! is_dir(filename: $path)) {
            return true;
        }

        $this->clear(path: $path);

        if (! rmdir(directory: $path)) {
            throw new DirectoryDeleteFailed(path: $path);
        }

        return true;
    }

    private function clear(string $path) : void
    {
        foreach (new FilesystemIterator(directory: $path, flags: FilesystemIterator::SKIP_DOTS) as $item) {
            $itemPath = $item->getPathname();

            if ($item->isDir()) {
                $this->execute(path: $itemPath);
            } else {
                unlink(filename: $itemPath);
            }
        }
    }
}