<?php

declare(strict_types=1);

namespace Avax\HTTP\Router\Cache;

use FilesystemIterator;
use RecursiveDirectoryIterator;
use RecursiveIteratorIterator;
use RuntimeException;
use SplFileInfo;

final class RouteFileLocator
{
    /** @return list<SplFileInfo> */
    public function discover(string $baseDir) : array
    {
        if (! is_dir(filename: $baseDir) || ! is_readable(filename: $baseDir)) {
            throw new RuntimeException(message: "Routes directory '{$baseDir}' is not accessible.");
        }

        $iterator = new RecursiveIteratorIterator(
            iterator: new RecursiveDirectoryIterator(
                directory: $baseDir,
                flags    : FilesystemIterator::SKIP_DOTS
            )
        );

        $files = array_filter(
            array   : iterator_to_array(iterator: $iterator),
            callback: static fn(SplFileInfo $file) : bool => $file->isFile()
                && $file->isReadable()
                && preg_match(
                    pattern: '/\.routes\.php$|^routes\.php$/',
                    subject: $file->getFilename()
                )
        );

        return array_values(array: $files);
    }
}
