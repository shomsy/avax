<?php

declare(strict_types=1);

namespace Avax\Components\Application\Container\System\Capabilities\Runtime\Scopes\Lifetimes;

/**
 * Marker for one shared container lifetime.
 */
final class SingletonLifetime
{
    public const string NAME = 'shared';
}
