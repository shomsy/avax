<?php

declare(strict_types=1);

namespace Avax\Components\Identity\Tenancy\System\Capabilities\Resolution;

use Psr\Http\Message\RequestInterface;

final readonly class TenantResolver
{
    public static function resolve(RequestInterface $request) : string
    {
        if ($tenantId = self::resolveFromDomain($request)) {
            return $tenantId;
        }

        if ($tenantId = self::resolveFromHeader($request)) {
            return $tenantId;
        }

        if ($tenantId = self::resolveFromPath($request)) {
            return $tenantId;
        }

        return 'default';
    }

    private static function resolveFromDomain(RequestInterface $request) : string|null
    {
        return DomainResolver::resolve($request);
    }

    private static function resolveFromHeader(RequestInterface $request) : string|null
    {
        return HeaderResolver::resolve($request);
    }

    private static function resolveFromPath(RequestInterface $request) : string|null
    {
        return PathResolver::resolve($request);
    }
}
