<?php

declare(strict_types=1);

namespace Avax\Components\Application\Filesystem\Paths;

class PathIsDirectory
{
    public function execute(string $path) : bool
    {
        return is_dir(filename: $path);
    }
}