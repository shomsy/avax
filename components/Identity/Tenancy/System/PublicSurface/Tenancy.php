<?php

declare(strict_types=1);

namespace Avax\Components\Identity\Tenancy\System\PublicSurface;

use Avax\Components\Identity\Tenancy\System\Capabilities\TenancyRuntime\TenancyRuntime;
use Closure;
use Psr\Http\Message\RequestInterface;

final readonly class Tenancy
{
    public function __construct(
        private TenancyRuntime $runtime,
    ) {}

    public function resolve(RequestInterface $request) : string
    {
        return $this->runtime->resolve(request: $request);
    }

    public function currentTenant() : string|null
    {
        return $this->runtime->currentTenant();
    }

    public function requireTenant() : string
    {
        return $this->runtime->requireTenant();
    }

    public function getTenantId() : string|null
    {
        return $this->currentTenant();
    }

    public function setTenantId(string $tenantId) : void
    {
        $this->runtime->setTenantId(tenantId: $tenantId);
    }

    public function clearTenant() : void
    {
        $this->runtime->clearTenant();
    }

    public function run(string $tenantId, Closure $operation) : mixed
    {
        return $this->runtime->run(
            tenantId : $tenantId,
            operation: $operation,
        );
    }

    public function switch(string $tenantId) : void
    {
        $this->runtime->setTenantId(tenantId: $tenantId);
    }

    public function admin() : Admin
    {
        return $this->runtime->admin();
    }
}
