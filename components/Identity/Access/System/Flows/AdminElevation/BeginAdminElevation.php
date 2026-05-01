<?php

declare(strict_types=1);

namespace Avax\Components\Identity\Access\System\Flows\AdminElevation;

use Avax\Framework\Foundation\Exception\NotImplementedException;

/**
 * BeginAdminElevation - Flow to initiate temporary admin privilege elevation.
 */
final readonly class BeginAdminElevation
{
    public function execute() : never
    {
        throw new NotImplementedException('Admin elevation workflow not yet implemented');
    }
}
