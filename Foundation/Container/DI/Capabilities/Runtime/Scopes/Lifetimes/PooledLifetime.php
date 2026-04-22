<?php

declare(strict_types=1);

namespace Avax\Container\DI\Capabilities\Runtime\Scopes\Lifetimes;

/**
 * Keeps reusable instances in one bounded runtime pool and checks them out per scope.
 */
final class PooledLifetime
{
    public const string NAME = 'pooled';
}
