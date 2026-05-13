<?php

declare(strict_types=1);

namespace Avax\Components\Application\Filesystem\System\Flows\ReadFileSize;

final class ReadFileSize
{
    public function execute(string $path) : int|false
    {
        if (! is_file($path)) {
            return false;
        }

        return filesize($path);
    }
}
