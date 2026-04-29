<?php

declare(strict_types=1);

namespace Avax\Components\Application\Container\System\Capabilities\Runtime\Scopes\Lifetimes;

/**
 * Marker for one always-new lifetime.
 */
final class TransientLifetime
{
    public const string NAME = 'transient';
}
