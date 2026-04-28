<?php

declare(strict_types=1);

namespace Avax\Components\Identity\Auth\System\Capabilities\Tenancy\Runtime\TenantSecurity\ReadConfiguration;

use Avax\Components\Identity\Auth\System\Capabilities\Tenancy\Security\TenantSecurityConfiguration;
use Avax\Components\Identity\Auth\System\Capabilities\Tenancy\Security\TenantSecurityConfigurationStoreInterface;

final readonly class ReadTenantSecurityConfiguration
{
    public function __construct(private TenantSecurityConfigurationStoreInterface $configurationStore) {}

    public function execute(string $tenantSlug) : TenantSecurityConfiguration|null
    {
        return $this->configurationStore->find(tenantSlug: $tenantSlug);
    }
}
