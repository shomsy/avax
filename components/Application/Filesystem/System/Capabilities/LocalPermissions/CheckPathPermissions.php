<?php

declare(strict_types=1);

namespace Avax\Components\Application\Filesystem\System\Capabilities\LocalPermissions;

final readonly class CheckPathPermissions
{
    public function execute(string $path) : ?int
    {
        if (! file_exists($path)) {
            return null;
        }

        return fileperms($path) & 0o777;
    }
}