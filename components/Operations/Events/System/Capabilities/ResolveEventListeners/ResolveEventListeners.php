<?php

declare(strict_types=1);

namespace Avax\Components\Operations\Events\System\Capabilities\ResolveEventListeners;

use Avax\Components\Application\Container\System\Capabilities\ResolveCallable\CallableResolutionFailed;
use Avax\Components\Application\Container\System\Capabilities\ResolveCallable\ResolveCallable;
use Avax\Components\Operations\Events\System\Foundation\CompiledListener;
use Psr\Container\ContainerInterface;

/**
 * Resolves compiled listener entries into invokable callables.
 *
 * Delegates class-string resolution to the central ResolveCallable,
 * which supports optional PSR-11 container injection.
 *
 * Handles both:
 * - Already-resolved callables (from DSL compilation)
 * - Class-string references (from attribute compilation)
 */
final class ResolveEventListeners
{
    private ResolveCallable $resolveCallable;

    public function __construct(ContainerInterface|null $container = null)
    {
        $this->resolveCallable = new ResolveCallable($container);
    }

    /**
     * Resolve a compiled listener entry into an invokable callable.
     *
     * @throws CallableResolutionFailed If a class-string listener cannot be resolved.
     */
    public function resolve(CompiledListener $compiled): callable
    {
        $listener = $compiled->listener;

        // Delegate to central resolver — handles class-string and callable.
        return $this->resolveCallable->resolve($listener);
    }
}
