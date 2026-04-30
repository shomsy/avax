<?php

declare(strict_types=1);

namespace Avax\Components\Identity\Access\System\Capabilities\Permissions;

use Avax\Components\Identity\Access\System\PublicSurface\Access;

final class GrantPermission
{
    public static function execute(string $p) : void
    {
        Access::grant($p);
    }
}
