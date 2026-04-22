<?php

declare(strict_types=1);

namespace Avax\Container\DI\Capabilities\Runtime\Scopes\Lifetimes;

/**
 * Marker for one operation-scoped lifetime.
 */
final class OperationLifetime
{
    public const string NAME = 'operation';
}
