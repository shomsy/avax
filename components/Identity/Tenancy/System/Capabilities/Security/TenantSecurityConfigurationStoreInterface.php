<?php

declare(strict_types=1);

namespace Avax\Components\Identity\Tenancy\System\Capabilities\Security;

interface TenantSecurityConfigurationStoreInterface
{
    public function save(TenantSecurityConfiguration $tenantSecurityConfiguration): void;

    public function find(string $tenantSlug): ?TenantSecurityConfiguration;
}
