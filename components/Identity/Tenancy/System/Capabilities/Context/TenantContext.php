<?php

declare(strict_types=1);

namespace Avax\Components\Identity\Tenancy\System\Capabilities\Context;

use Closure;

/**
 * Request-scoped tenant context.
 *
 * @deprecated Inject TenantContextInterface directly instead of using this static facade.
 *             This class remains for backward compatibility but the static state
 *             is now owned by a configurable TenantContextInterface instance.
 */
final class TenantContext
{
    private static ?TenantContextInterface $context = null;

    /**
     * Replace the backing context implementation (for DI integration).
     */
    public static function setContext(TenantContextInterface $context) : void
    {
        self::$context = $context;
    }

    public static function current() : string|null
    {
        return self::resolveContext()->current();
    }

    public static function set(string $tenantId) : void
    {
        self::resolveContext()->set($tenantId);
    }

    public static function clear() : void
    {
        self::resolveContext()->clear();
    }

    public static function with(string $tenantId, Closure $operation) : mixed
    {
        return self::resolveContext()->with($tenantId, $operation);
    }

    private static function resolveContext() : TenantContextInterface
    {
        if (self::$context === null) {
            self::$context = new DefaultTenantContext();
        }

        return self::$context;
    }

    /**
     * Reset static state for long-lived worker safety.
     * MUST be called between requests in persistent runtimes.
     */
    public static function reset() : void
    {
        self::$context = null;
    }
}
