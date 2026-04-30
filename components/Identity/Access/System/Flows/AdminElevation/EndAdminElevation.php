<?php

declare(strict_types=1);

namespace Avax\Components\Identity\Access\System\Flows\AdminElevation;

use Avax\Framework\Foundation\Exception\NotImplementedException;

/**
 * EndAdminElevation - Flow to revoke temporary admin privilege elevation.
 */
final readonly class EndAdminElevation
{
    public function execute() : void
    {
        throw new NotImplementedException('Admin elevation revocation workflow not yet implemented');
    }
}
