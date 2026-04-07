<?php

declare(strict_types=1);

namespace Avax\Container\DependencyInjection\Capability\Scopes\Lifetimes\Strategies;

use Avax\Container\DependencyInjection\Capability\Scopes\Lifetimes\Contracts\LifecycleStrategy;

/**
 * Transient Lifecycle Strategy
 *
 */
final class TransientLifecycleStrategy implements LifecycleStrategy
{
    /**
     * No-op store; transient services are not cached.
     *
     * @param string $abstract Service identifier
     * @param mixed  $instance Instance (ignored)
     *
     */
    public function store(string $abstract, mixed $instance) : void {}

    /**
     * Transients are never cached.
     *
     * @param string $abstract Service identifier
     *
     * @return bool Always false
     *
     */
    public function has(string $abstract) : bool
    {
        return false;
    }

    /**
     * Transients are never retrieved (always null).
     *
     * @param string $abstract Service identifier
     *
     * @return mixed|null Always null
     *
     */
    public function retrieve(string $abstract) : mixed
    {
        return null;
    }

    /**
     * No-op clear; nothing is stored.
     *
     */
    public function clear() : void {}
}
