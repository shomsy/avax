<?php

declare(strict_types=1);

namespace Avax\Components\Identity\Tenancy\System\Capabilities\Resolution;

use Psr\Http\Message\RequestInterface;

final readonly class TenantResolver
{
    public static function resolve(RequestInterface $request): string
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

    private static function resolveFromDomain(RequestInterface $request): ?string
    {
        return DomainResolver::resolve($request);
    }

    private static function resolveFromHeader(RequestInterface $request): ?string
    {
        return HeaderResolver::resolve($request);
    }

    private static function resolveFromPath(RequestInterface $request): ?string
    {
        return PathResolver::resolve($request);
    }
}

final readonly class DomainResolver
{
    public static function resolve(RequestInterface $request): ?string
    {
        $host = $request->getUri()->getHost();
        $parts = explode('.', $host);

        if (count($parts) > 2) {
            return $parts[0];
        }

        return null;
    }
}

final readonly class HeaderResolver
{
    public static function resolve(RequestInterface $request): ?string
    {
        return $request->getHeaderLine('X-Tenant-ID') ?: null;
    }
}

final readonly class PathResolver
{
    public static function resolve(RequestInterface $request): ?string
    {
        $path = $request->getUri()->getPath();
        $parts = explode('/', $path);

        if (count($parts) >= 2 && $parts[1] === 'tenants') {
            return $parts[2] ?? null;
        }

        return null;
    }
}
