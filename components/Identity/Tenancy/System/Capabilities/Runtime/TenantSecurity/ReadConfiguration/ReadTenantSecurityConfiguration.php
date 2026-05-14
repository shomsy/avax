<?php

declare(strict_types=1);

namespace Avax\Components\Identity\Tenancy\System\Capabilities\Runtime\TenantSecurity\ReadConfiguration;

use Avax\Components\Identity\Tenancy\System\Capabilities\Security\TenantSecurityConfiguration;
use Avax\Components\Identity\Tenancy\System\Capabilities\Security\TenantSecurityConfigurationStoreInterface;

final readonly class ReadTenantSecurityConfiguration
{
    public function __construct(private TenantSecurityConfigurationStoreInterface $tenantSecurityConfigurationStore) {}

    public function execute(string $tenantSlug) : TenantSecurityConfiguration|null
    {
        return $this->tenantSecurityConfigurationStore->find(tenantSlug: $tenantSlug);
    }
}
