<?php

declare(strict_types=1);

namespace Avax\Components\Application\Container\System\Capabilities\Runtime\Scopes\Lifetimes;

/**
 * Marker for one job-scoped lifetime.
 */
final class JobLifetime
{
    public const string NAME = 'job';
}
