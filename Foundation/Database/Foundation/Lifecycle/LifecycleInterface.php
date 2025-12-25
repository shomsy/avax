<?php

declare(strict_types=1);

namespace Avax\Database\Lifecycle;

/**
 * Defines the contract for high-level component lifecycle management.
 *
 * -- intent: ensure all database features follow a predictable registration and boot cycle.
 */
interface LifecycleInterface
{
    /**
     * Register the component and its services into the system container.
     *
     * -- intent: declare service recipes and bindings before the system starts.
     *
     * @return void
     */
    public function register() : void;

    /**
     * Perform bootstrap operations after all system components are ready.
     *
     * -- intent: initialize internal state or dependencies that require a fully-registered container.
     *
     * @return void
     */
    public function boot() : void;

    /**
     * Gracefully terminate the component and release active resources.
     *
     * -- intent: clean up persistent connections, file handles, or caches.
     *
     * @return void
     */
    public function shutdown() : void;
}
