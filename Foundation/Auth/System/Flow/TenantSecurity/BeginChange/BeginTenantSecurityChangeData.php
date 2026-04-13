<?php

declare(strict_types=1);

namespace Avax\Auth\System\Flow\TenantSecurity\BeginChange;

use Avax\Auth\System\Capability\TenantSecurity\TenantSecurityConfiguration;

final readonly class BeginTenantSecurityChangeData
{
    public function __construct(
        public string $tenantSlug,
        public string $requestedBy,
        public string $reason,
        public TenantSecurityConfiguration $after
    ) {}
}
