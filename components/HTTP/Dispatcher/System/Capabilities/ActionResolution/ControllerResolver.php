<?php

declare(strict_types=1);

namespace Avax\Components\HTTP\Dispatcher\System\Capabilities\ActionResolution;

use Avax\Components\Application\Container\System\Capabilities\ResolveCallable\ResolveCallable;

/**
 * ControllerResolver - Resolves controller instances through DI container or ResolveCallable.
 *
 * Never falls back to direct `new $className()` — uses ResolveCallable which
 * provides proper diagnostics when constructor dependencies cannot be resolved.
 */
final readonly class ControllerResolver
{
    public function __construct(
        private ResolveCallable $resolver,
    ) {}

    public function resolve(string $className) : callable
    {
        return $this->resolver->resolve($className);
    }
}
