<?php

declare(strict_types=1);

namespace Avax\Components\Identity\Tenancy\System\Capabilities\Context;

use Closure;

/**
 * Tenant context storage contract — request-scoped tenant identification.
 *
 * Implementations must ensure per-request isolation in long-lived workers.
 */
interface TenantContextInterface
{
    public function current() : string|null;

    public function set(string $tenantId) : void;

    public function clear() : void;

    public function with(string $tenantId, Closure $operation) : mixed;
}
