<?php

declare(strict_types=1);

namespace Avax\Components\Tenancy\System\PublicSurface;

use Avax\Components\Tenancy\System\Capabilities\Context\TenantContext;
use Avax\Components\Tenancy\System\Capabilities\Resolution\TenantResolver;
use Closure;
use Psr\Http\Message\RequestInterface;

final class Tenancy
{
    private static TenantContext $context;

    public static function resolve(RequestInterface $request) : string
    {
        return TenantResolver::resolve($request);
    }

    public static function current() : string|null
    {
        return self::context()->current();
    }

    private static function context() : TenantContext
    {
        if (! isset(self::$context)) {
            self::$context = new TenantContext();
        }

        return self::$context;
    }

    public static function run(string $tenantId, Closure $operation) : mixed
    {
        $previous = self::context()->set($tenantId);

        try {
            return $operation();
        } finally {
            self::context()->restore($previous);
        }
    }

    public static function switch(string $tenantId) : void
    {
        self::context()->set($tenantId);
    }
}