<?php

declare(strict_types=1);

namespace components\Container\DI\Capabilities\Runtime\Scopes\Lifetimes;

/**
 * Marker for one active-scope lifetime.
 */
final class ScopedLifetime
{
    public const string NAME = 'scoped';
}
