<?php

declare(strict_types=1);

namespace Avax\Components\Application\Filesystem\System\Capabilities\LocalPermissions;

final readonly class CheckPathIsReadable
{
    public function execute(string $path) : bool
    {
        return file_exists($path) && is_readable($path);
    }
}