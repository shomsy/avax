<?php

declare(strict_types=1);

namespace Avax\Components\Application\Filesystem\System\Capabilities\LocalPermissions;

final readonly class CheckPathIsWritable
{
    public function execute(string $path) : bool
    {
        if (file_exists($path)) {
            return is_writable($path);
        }

        $dir = dirname($path);
        if (is_dir($dir)) {
            return is_writable($dir);
        }

        return false;
    }
}