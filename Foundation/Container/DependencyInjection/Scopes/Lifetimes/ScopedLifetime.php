<?php

declare(strict_types=1);

namespace Avax\Container\DependencyInjection\Scopes\Lifetimes;

/**
 * Marker for one active-scope lifetime.
 */
final class ScopedLifetime
{
    public const string NAME = 'scoped';
}
