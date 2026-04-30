<?php

declare(strict_types=1);

namespace Avax\Components\Identity\Access\System\Capabilities\Permissions;

use Avax\Components\Identity\Access\System\PublicSurface\Access;

final class CheckPermission
{
    public static function execute(string $p) : bool
    {
        return Access::hasPermission($p);
    }
}
