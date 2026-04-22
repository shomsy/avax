<?php

declare(strict_types=1);

namespace Avax\Container\DI\Capabilities\Runtime\Scopes\Lifetimes;

/**
 * Marker for one always-new lifetime.
 */
final class TransientLifetime
{
    public const string NAME = 'transient';
}
