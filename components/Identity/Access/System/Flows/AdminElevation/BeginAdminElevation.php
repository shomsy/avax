<?php

declare(strict_types=1);

namespace Avax\Components\Identity\Access\System\Flows\AdminElevation;

use Avax\Components\Identity\Access\System\Capabilities\AdminElevation\AdminElevationStore;

/**
 * BeginAdminElevation — Flow to initiate temporary admin privilege elevation.
 */
final class BeginAdminElevation
{
    public function __construct(
        private AdminElevationStore $store = new AdminElevationStore(),
    ) {}

    public function execute() : void
    {
        $this->store->elevate();
    }

    public function isActive() : bool
    {
        return $this->store->isActive();
    }

    public function reset() : void
    {
        $this->store->reset();
    }
}
