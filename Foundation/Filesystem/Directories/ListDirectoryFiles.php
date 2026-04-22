<?php

declare(strict_types=1);

namespace Avax\Filesystem\Directories;

use Avax\Filesystem\Disks\Disk;
use FilesystemIterator;
use SplFileInfo;

class ListDirectoryFiles
{
    private Disk $disk;

    public function __construct(Disk $disk) { $this->disk = $disk; }

    public function execute(string $path) : array
    {
        if (! is_dir(filename: $path) || ! is_readable(filename: $path)) {
            return [];
        }

        try {
            $iterator = new FilesystemIterator(
                directory: $path,
                flags    : FilesystemIterator::SKIP_DOTS
            );

            return array_map(
                callback: static fn (SplFileInfo $file) => $file->getPathname(),
                array   : iterator_to_array(iterator: $iterator, preserve_keys: false)
            );
        } catch (\Throwable) {
            return [];
        }
    }
}