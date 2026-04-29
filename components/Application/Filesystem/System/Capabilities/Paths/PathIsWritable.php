<?php

declare(strict_types=1);

namespace Avax\Components\Application\Filesystem\Paths;

class PathIsWritable
{
    public function execute(string $path) : bool
    {
        return is_writable(filename: $path);
    }
}