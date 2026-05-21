<?php

declare(strict_types=1);

namespace Avax\Components\Identity\Access\System\Flows\AdminElevation;

/**
 * EndAdminElevation — Flow to revoke temporary admin privilege elevation.
 */
final readonly class EndAdminElevation
{
    public function __construct(
        private BeginAdminElevation $beginAdminElevation,
    ) {}

    public function execute() : void
    {
        $this->beginAdminElevation->reset();
    }
}
