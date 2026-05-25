<?php

declare(strict_types=1);

namespace Avax\Components\Identity\Flows\BeginTenantContext;

use Avax\Components\Identity\Capabilities\Tenants\TenantDirectory;
use Avax\Components\Identity\Foundation\Failures\AccessDenied;

final readonly class BeginTenantContext
{
    public function __construct(private TenantDirectory $tenants) {}

    public function begin(TenantContextRequest $request): TenantContext
    {
        if (! $this->tenants->userBelongsToTenant($request->userId(), $request->tenantId())) {
            throw AccessDenied::because('User does not belong to requested tenant.');
        }

        return new TenantContext($request->userId(), $request->tenantId());
    }
}
