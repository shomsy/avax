<?php

declare(strict_types=1);

namespace Avax\Components\Identity\Tenancy\System\System\Capabilities\Runtime\TenantSecurity\BeginChange;

use Avax\Components\Identity\Tenancy\System\System\Capabilities\Security\TenantSecurityConfiguration;

final readonly class BeginTenantSecurityChangeData
{
    public function __construct(public string $tenantSlug, public string $requestedBy, public string $reason, public TenantSecurityConfiguration $after) {}
}
