<?php

declare(strict_types=1);

namespace Avax\Components\Identity\Access\System\Flows\AdminElevation;

/**
 * BeginAdminElevation - Flow to initiate temporary admin privilege elevation.
 */
final readonly class BeginAdminElevation
{
    public function execute() : void
    {
        // Logic to challenge user for elevation (e.g. password re-entry)
        // and set session state.
    }
}
