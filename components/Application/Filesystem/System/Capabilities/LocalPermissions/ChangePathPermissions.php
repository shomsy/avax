<?php

declare(strict_types=1);

namespace Avax\Components\Application\Filesystem\System\Capabilities\LocalPermissions;

use Avax\Components\Application\Filesystem\System\Foundation\Failure\PermissionDenied;

final readonly class ChangePathPermissions
{
    public function execute(string $path, int $permissions) : bool
    {
        if (! file_exists($path)) {
            return false;
        }

        $result = chmod($path, $permissions);

        if (! $result) {
            throw new PermissionDenied($path, 'chmod');
        }

        return true;
    }
}