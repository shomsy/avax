<?php

declare(strict_types=1);

namespace Avax\Components\Identity\Tenancy\System\PublicSurface;

use Avax\Components\Identity\Tenancy\System\Capabilities\Context\TenantContext;
use Avax\Components\Identity\Tenancy\System\Capabilities\Resolution\TenantResolver;
use Closure;
use Psr\Http\Message\RequestInterface;

final class Tenancy
{
    public static function resolve(RequestInterface $request) : string
    {
        return TenantResolver::resolve($request);
    }

    public static function getTenantId() : string|null
    {
        return TenantContext::current();
    }

    public static function setTenantId(string $tenantId) : void
    {
        TenantContext::set($tenantId);
    }

    public static function clearTenant() : void
    {
        TenantContext::clear();
    }

    public static function run(string $tenantId, Closure $operation) : mixed
    {
        return TenantContext::with($tenantId, $operation);
    }

    public static function switch(string $tenantId) : void
    {
        TenantContext::set($tenantId);
    }
}