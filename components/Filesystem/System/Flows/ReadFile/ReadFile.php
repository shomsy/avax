<?php

declare(strict_types=1);

namespace Avax\Components\Filesystem\System\Flows\ReadFile;

use Avax\Components\Filesystem\System\Capabilities\Disks\Disk;

final class ReadFile
{
    public function read(Disk $disk, string $path): string
    {
        $fullPath = $disk->path($path);

        return file_get_contents($fullPath);
    }
}