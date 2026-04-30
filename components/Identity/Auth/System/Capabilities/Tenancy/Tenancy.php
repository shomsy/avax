<?php

declare(strict_types=1);

namespace Avax\Components\Identity\Auth\System\Capabilities\Tenancy;

use Avax\Components\Identity\Auth\System\Capabilities\Tenancy\Security\Security;
use Avax\Components\Identity\Auth\System\Capabilities\Tenancy\Tenants\Tenants;
use SensitiveParameter;

/**
 * Tenancy capability coordinator.
 *
 * Exposes accessors for Tenant management and Security configuration.
 */
final readonly class Tenancy
{
    public function __construct(
        private Tenants  $tenants,
        #[SensitiveParameter]
        private Security $security,
    ) {}

    public function tenants() : Tenants
    {
        return $this->tenants;
    }

    public function security() : Security
    {
        return $this->security;
    }
}
