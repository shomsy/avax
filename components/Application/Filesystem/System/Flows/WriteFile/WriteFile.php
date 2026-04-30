<?php

declare(strict_types=1);

namespace Avax\Components\Application\Filesystem\System\Flows\WriteFile;

use Avax\Components\Application\Filesystem\System\Capabilities\Disks\Disk;

final class WriteFile
{
    public function write(Disk $disk, string $path, string $contents) : void
    {
        $fullPath = $disk->path($path);

        file_put_contents($fullPath, $contents);
    }
}
