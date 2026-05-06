<?php

declare(strict_types=1);

namespace Avax\Components\Application\Filesystem\System\Capabilities\Paths;

class PathHasPermissions
{
    public function execute(string $path, int $permissions): bool
    {
        if (! file_exists(filename: $path)) {
            return false;
        }

        $actualPermissions = fileperms(filename: $path) & 0o777;

        return $actualPermissions === $permissions;
    }
}
