<?php

declare(strict_types=1);

namespace Avax\Auth\System\Flow\TenantSecurity\BeginChange;

use Avax\Auth\System\Capability\TenantSecurity\TenantSecurityConfiguration;

final readonly class BeginTenantSecurityChangeData
{
    public TenantSecurityConfiguration $after;
    public string                      $reason;
    public string                      $requestedBy;
    public string                      $tenantSlug;

    public function __construct(
        string                      $tenantSlug,
        string                      $requestedBy,
        string                      $reason,
        TenantSecurityConfiguration $after
    )
    {
        $this->tenantSlug  = $tenantSlug;
        $this->requestedBy = $requestedBy;
        $this->reason      = $reason;
        $this->after       = $after;
    }
}
