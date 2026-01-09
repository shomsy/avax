<?php

declare(strict_types=1);

namespace Avax\Container\Features\Core\Contracts;

use Psr\Container\ContainerInterface as PsrContainerInterface;

/**
 * Core resolution interface for the container.
 * Focuses on retrieval and execution (Runtime).
 */
interface ContainerInterface extends PsrContainerInterface
{
    /**
     * Resolve the given type from the container with parameter overrides.
     */
    public function make(string $abstract, array $parameters = []): object;

    /**
     * Call a closure or class method with automated dependency resolution.
     */
    public function call(callable|string $callable, array $parameters = []): mixed;

    /**
     * Populate an existing object instance with its required dependencies.
     */
    public function injectInto(object $target): object;

    /**
     * Check if dependencies can be injected into the target object.
     */
    public function canInject(object $target): bool;

    /**
     * Begin a new scope for scoped services.
     */
    public function beginScope(): void;

    /**
     * End the current scope and dispose of scoped services.
     */
    public function endScope(): void;

    /**
     * Register an existing instance as a shared service (Allowed at runtime for flexibility).
     */
    public function instance(string $abstract, object $instance): void;
}
