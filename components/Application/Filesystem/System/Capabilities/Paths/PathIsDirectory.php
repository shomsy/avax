<?php

declare(strict_types=1);

namespace Avax\Components\Application\Filesystem\System\Capabilities\Paths;

class PathIsDirectory
{
    public function execute(string $path) : bool
    {
        return is_dir(filename: $path);
    }
}
