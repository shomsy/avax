<?php

declare(strict_types=1);

namespace Avax\Container\DependencyInjection\Scopes\Lifetimes;

/**
 * Marker for one job-scoped lifetime.
 */
final class JobLifetime
{
    public const string NAME = 'job';
}
