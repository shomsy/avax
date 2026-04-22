<?php

declare(strict_types=1);

namespace Avax\Container\DI\Capabilities\Runtime\Scopes\Lifetimes;

/**
 * Marker for one request-scoped lifetime.
 */
final class RequestLifetime
{
    public const string NAME = 'request';
}
