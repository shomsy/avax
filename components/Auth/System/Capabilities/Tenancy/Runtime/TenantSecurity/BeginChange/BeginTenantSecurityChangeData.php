<?php

declare(strict_types=1);

namespace Avax\Auth\System\Capabilities\Tenancy\Runtime\TenantSecurity\BeginChange;

use Avax\Auth\System\Capabilities\Tenancy\Security\TenantSecurityConfiguration;

final readonly class BeginTenantSecurityChangeData
{
    public function __construct(public string $tenantSlug, public string $requestedBy, public string $reason, public TenantSecurityConfiguration $after) {}
}
