<?php

declare(strict_types=1);

namespace Avax\Components\Identity\Tenancy\System\Capabilities\TenancyRuntime;

use Avax\Components\Identity\Tenancy\System\Capabilities\Context\TenantContextInterface;
use Avax\Components\Identity\Tenancy\System\Capabilities\Resolution\TenantResolver;
use Avax\Components\Identity\Tenancy\System\Foundation\Failure\TenantNotFoundException;
use Avax\Components\Identity\Tenancy\System\PublicSurface\Admin;
use Closure;
use Psr\Http\Message\RequestInterface;

/**
 * TenancyRuntime owns tenant context state for one assembled runtime.
 */
final readonly class TenancyRuntime
{
    public function __construct(
        private TenantContextInterface $tenantContext,
        private Admin                  $admin,
    ) {}

    public function resolve(RequestInterface $request) : string
    {
        return TenantResolver::resolve(request: $request);
    }

    public function currentTenant() : string|null
    {
        return $this->tenantContext->current();
    }

    public function requireTenant() : string
    {
        $tenantId = $this->currentTenant();

        if ($tenantId === null) {
            throw new TenantNotFoundException(
                identifier: 'current tenant',
                message   : 'Tenant context is required.',
            );
        }

        return $tenantId;
    }

    public function setTenantId(string $tenantId) : void
    {
        $this->tenantContext->set(tenantId: $tenantId);
    }

    public function clearTenant() : void
    {
        $this->tenantContext->clear();
    }

    public function run(string $tenantId, Closure $operation) : mixed
    {
        return $this->tenantContext->with(
            tenantId : $tenantId,
            operation: $operation,
        );
    }

    public function admin() : Admin
    {
        return $this->admin;
    }
}
