<?php

declare(strict_types=1);

namespace Avax\Components\Application\Filesystem\System\Capabilities\Paths;

use RuntimeException;

class ChangePathPermissions
{
    public function execute(string $path, int $permissions): bool
    {
        if (! file_exists(filename: $path)) {
            throw new RuntimeException(message: 'Path does not exist: ' . $path);
        }

        if (! chmod(filename: $path, permissions: $permissions)) {
            error_log(message: 'Failed to set permissions on: ' . $path);

            return false;
        }

        return true;
    }
}
