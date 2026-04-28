<?php

declare(strict_types=1);

namespace Avax\Components\Application\Container\DI\Capabilities\Runtime\Scopes\Lifetimes;

/**
 * Marker for one tenant-scoped lifetime.
 */
final class TenantLifetime
{
    public const string NAME = 'tenant';
}
