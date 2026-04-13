<?php

declare(strict_types=1);

namespace Avax\Auth\System\Capability\TenantSecurity;

interface TenantSecurityConfigurationStoreInterface
{
    public function save(TenantSecurityConfiguration $configuration) : void;

    public function find(string $tenantSlug) : TenantSecurityConfiguration|null;
}
