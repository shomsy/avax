<?php

declare(strict_types=1);

namespace Avax\Container\Features\Operate\Shutdown;

use Avax\Container\Features\Operate\Scope\ScopeManager;

/**
 * Orchestrator for container graceful shutdown and memory cleanup.
 *
 * This action is responsible for purging all scoped and singleton instances from the
 * container's runtime memory. It is a critical component for preventing memory leaks
 * in long-running processes (e.g., Workers, Sockets, Daemons) by ensuring a clean
 * state between execution cycles.
 *
 * @package Avax\Container\Features\Operate\Shutdown
 * @see docs/Features/Operate/Shutdown/TerminateContainer.md
 */
final readonly class TerminateContainer
{
    /**
     * Initializes the terminator with a scope management facade.
     *
     * @param ScopeManager $manager The manager responsible for instance storage.
     * @see docs/Features/Operate/Shutdown/TerminateContainer.md#method-__construct
     */
    public function __construct(private ScopeManager $manager) {}

    /**
     * Executes the termination sequence.
     *
     * Clears all registered instances within the scope registry, effectively
     * resetting the container to its post-bootstrap, pre-resolution state.
     *
     * @return void
     * @see docs/Features/Operate/Shutdown/TerminateContainer.md#method-__invoke
     */
    public function __invoke(): void
    {
        $this->manager->terminate();
    }
}
