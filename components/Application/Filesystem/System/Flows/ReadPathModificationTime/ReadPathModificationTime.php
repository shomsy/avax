<?php

declare(strict_types=1);

namespace Avax\Components\Application\Filesystem\System\Flows\ReadPathModificationTime;

final class ReadPathModificationTime
{
    public function execute(string $path) : int|false
    {
        if (! is_file($path)) {
            return false;
        }

        return filemtime($path);
    }
}
