<?php

declare(strict_types=1);

namespace Avax\Container\DependencyInjection\Scopes\Lifetimes;

/**
 * Marker for one tenant-scoped lifetime.
 */
final class TenantLifetime
{
    public const string NAME = 'tenant';
}
