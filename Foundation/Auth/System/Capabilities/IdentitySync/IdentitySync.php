<?php

declare(strict_types=1);

namespace Avax\Auth\System\Capabilities\IdentitySync;

use Avax\Auth\System\Capabilities\IdentitySync\Provisioning\Provisioning;
use Avax\Auth\System\Capabilities\IdentitySync\SCIM\SCIM;

/**
 * Identity Synchronization capability coordinator.
 *
 * Exposes accessors for SCIM directory management and Provisioning operations.
 */
final readonly class IdentitySync
{
    public function __construct(
        private SCIM         $scim,
        private Provisioning $provisioning
    ) {}

    public function scim() : SCIM
    {
        return $this->scim;
    }

    public function provisioning() : Provisioning
    {
        return $this->provisioning;
    }
}
