<?php

declare(strict_types=1);

namespace Avax\Filesystem\Paths;

class PathExists
{
    public function execute(string $path) : bool
    {
        return file_exists(filename: $path);
    }
}