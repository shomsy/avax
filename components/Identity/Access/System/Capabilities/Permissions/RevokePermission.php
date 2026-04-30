<?php

declare(strict_types=1);

namespace Avax\Components\Identity\Access\System\Capabilities\Permissions;

use Avax\Components\Identity\Access\System\PublicSurface\Access;

final class RevokePermission
{
    public static function execute(string $p) : void
    {
        Access::revoke($p);
    }
}
