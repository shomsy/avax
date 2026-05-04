<?php

declare(strict_types=1);

namespace Avax\Components\Application\Filesystem\System\Capabilities\Paths;

class PathExists
{
    public function execute(string $path) : bool
    {
        return file_exists(filename: $path);
    }
}
