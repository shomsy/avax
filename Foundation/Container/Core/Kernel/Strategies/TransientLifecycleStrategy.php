<?php

declare(strict_types=1);

namespace Avax\Container\Core\Kernel\Strategies;

use Avax\Container\Core\Kernel\Contracts\LifecycleStrategy;

/**
 * Transient Lifecycle Strategy
 *
 * @see docs_md/Core/Kernel/Strategies/TransientLifecycleStrategy.md#quick-summary
 */
final class TransientLifecycleStrategy implements LifecycleStrategy
{
    /**
     * No-op store; transient services are not cached.
     *
     * @param string $abstract Service identifier
     * @param mixed  $instance Instance (ignored)
     *
     * @return void
     * @see docs_md/Core/Kernel/Strategies/TransientLifecycleStrategy.md#method-storestring-abstract-mixed-instance-void
     */
    public function store(string $abstract, mixed $instance): void {}

    /**
     * Transients are never cached.
     *
     * @param string $abstract Service identifier
     *
     * @return bool Always false
     * @see docs_md/Core/Kernel/Strategies/TransientLifecycleStrategy.md#method-hasstring-abstract-bool
     */
    public function has(string $abstract): bool
    {
        return false;
    }

    /**
     * Transients are never retrieved (always null).
     *
     * @param string $abstract Service identifier
     *
     * @return mixed|null Always null
     * @see docs_md/Core/Kernel/Strategies/TransientLifecycleStrategy.md#method-retrievestring-abstract-mixed
     */
    public function retrieve(string $abstract): mixed
    {
        return null;
    }

    /**
     * No-op clear; nothing is stored.
     *
     * @return void
     * @see docs_md/Core/Kernel/Strategies/TransientLifecycleStrategy.md#method-clearvoid
     */
    public function clear(): void {}
}
