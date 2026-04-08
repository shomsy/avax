<?php

declare(strict_types=1);

namespace Avax\Container\DependencyInjection\Scopes\Lifetimes;

/**
 * Marker for one shared container lifetime.
 */
final class SharedLifetime
{
    public const string NAME = 'shared';
}
