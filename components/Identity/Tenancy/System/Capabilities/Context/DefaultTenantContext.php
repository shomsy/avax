<?php

declare(strict_types=1);

namespace Avax\Components\Identity\Tenancy\System\Capabilities\Context;

use Closure;

/**
 * Default instance-based tenant context.
 *
 * Use this for DI integration instead of the TenantContext static facade.
 */
final class DefaultTenantContext implements TenantContextInterface
{
    private ?string $current = null;

    public function current() : string|null
    {
        return $this->current;
    }

    public function set(string $tenantId) : void
    {
        $this->current = $tenantId;
    }

    public function clear() : void
    {
        $this->current = null;
    }

    public function with(string $tenantId, Closure $operation) : mixed
    {
        $previous = $this->current;

        try {
            $this->current = $tenantId;

            return $operation();
        } finally {
            $this->current = $previous;
        }
    }
}
