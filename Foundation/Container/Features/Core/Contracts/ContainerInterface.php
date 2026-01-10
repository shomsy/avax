<?php

declare(strict_types=1);

namespace Avax\Container\Features\Core\Contracts;

use Psr\Container\ContainerInterface as PsrContainerInterface;

/**
 * Core Container Resolution Interface
 *
 * Defines the primary contract for service resolution and dependency injection operations.
 * This interface extends PSR-11 ContainerInterface while adding advanced features like
 * parameter overrides, callable invocation, property injection, and scope management.
 * Focuses on runtime operations (retrieval and execution) rather than registration.
 *
 * @package Avax\Container\Features\Core\Contracts
 * @see docs/Features/Core/Contracts/ContainerInterface.md
 */
interface ContainerInterface extends PsrContainerInterface
{
    /**
     * Resolve the given type from the container with parameter overrides.
     *
     * @param string $abstract Service identifier or class name
     * @param array $parameters Manual parameter overrides
     * @return object The resolved service instance
     * @see docs/Features/Core/Contracts/ContainerInterface.md#method-make
     */
    public function make(string $abstract, array $parameters = []): object;

    /**
     * Call a closure or class method with automated dependency resolution.
     *
     * @param callable|string $callable Closure or class@method string
     * @param array $parameters Manual parameter overrides
     * @return mixed The result of the callable invocation
     * @see docs/Features/Core/Contracts/ContainerInterface.md#method-call
     */
    public function call(callable|string $callable, array $parameters = []): mixed;

    /**
     * Populate an existing object instance with its required dependencies.
     *
     * @param object $target The object to inject dependencies into
     * @return object The same object with injected dependencies
     * @see docs/Features/Core/Contracts/ContainerInterface.md#method-injectinto
     */
    public function injectInto(object $target): object;

    /**
     * Check if dependencies can be injected into the target object.
     *
     * @param object $target The object to check
     * @return bool True if injection is possible
     * @see docs/Features/Core/Contracts/ContainerInterface.md#method-caninject
     */
    public function canInject(object $target): bool;

    /**
     * Begin a new scope for scoped services.
     *
     * @return void
     * @see docs/Features/Core/Contracts/ContainerInterface.md#method-beginscope
     */
    public function beginScope(): void;

    /**
     * End the current scope and dispose of scoped services.
     *
     * @return void
     * @see docs/Features/Core/Contracts/ContainerInterface.md#method-endscope
     */
    public function endScope(): void;

    /**
     * Register an existing instance as a shared service (Allowed at runtime for flexibility).
     *
     * @param string $abstract Service identifier
     * @param object $instance The instance to register
     * @return void
     * @see docs/Features/Core/Contracts/ContainerInterface.md#method-instance
     */
    public function instance(string $abstract, object $instance): void;
}
