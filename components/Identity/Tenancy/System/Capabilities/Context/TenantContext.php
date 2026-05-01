<?php

declare(strict_types=1);

namespace Avax\Components\Identity\Tenancy\System\Capabilities\Context;

use Closure;

final class TenantContext
{
    private static ?string $current = null;

    public static function current(): ?string
    {
        return self::$current;
    }

    public static function set(string $tenantId): void
    {
        self::$current = $tenantId;
    }

    public static function clear(): void
    {
        self::$current = null;
    }

    public static function with(string $tenantId, Closure $operation): mixed
    {
        $previous = self::$current;

        try {
            self::$current = $tenantId;

            return $operation();
        } finally {
            self::$current = $previous;
        }
    }
}
