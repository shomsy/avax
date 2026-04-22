<?php

declare(strict_types=1);

namespace Avax\Container\DI\Capabilities\Declaration\Providers;

/**
 * Explicit contract for providers that register lazily on first owned service resolve.
 */
interface DeferredProviderInterface extends ServiceProviderInterface
{
    /**
     * Reports whether the provider is deferred.
     */
    public function deferred() : bool;

    /**
     * Returns the service ids owned by this deferred provider.
     *
     * @return list<string>
     */
    public function provides() : array;
}
