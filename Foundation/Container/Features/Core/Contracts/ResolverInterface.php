<?php

declare(strict_types=1);
namespace Avax\Container\Features\Core\Contracts;

use Avax\Container\Features\Core\Exceptions\ContainerExceptionInterface;
use Avax\Container\Features\Think\Model\ServicePrototype;

/**
 * @package Avax\Container\Core\Contracts
 *
 * Contract for service resolution and retrieval operations.
 *
 * ResolverInterface defines the core service resolution capabilities of the container.
 * It focuses solely on retrieving and resolving services, separating this concern
 * from injection, registration, and compilation operations.
 *
 * WHY IT EXISTS:
 * - To provide a focused contract for service resolution operations
 * - To enable dependency injection of resolution capabilities
 * - To support PSR-11 compatible resolution without full container features
 * - To facilitate testing and mocking of resolution behavior
 *
 * RESOLUTION SCENARIOS:
 * - Direct service retrieval by identifier
 * - LazyValue wrappers for explicit deferred resolution
 * - Parameter override support
 * - Context-aware resolution with scopes
 *
 * THREAD SAFETY:
 * Implementations should be thread-safe for concurrent resolution operations.
 *
 * @see     ContainerInterface For the full container contract
 * @see     InjectorInterface For dependency injection operations
 */
interface ResolverInterface
{
    /**
     * Checks if the resolver can provide an entry for the given identifier.
     *
     * This method should return true if the resolver knows how to resolve
     * the given identifier, even if the resolution might fail due to
     * runtime conditions or missing dependencies.
     *
     * @param string $id Identifier of the entry to check
     *
     * @return bool True if the identifier can be resolved, false otherwise
     */
    public function has(string $id) : bool;

    /**
     * Finds an entry of the container by its identifier and returns it.
     *
     * @param string $id Identifier of the entry to look for
     *
     * @return mixed The resolved service instance
     *
     * @throws ContainerExceptionInterface If resolution fails
     */
    public function get(string $id) : mixed;

    /**
     * Resolves a service prototype into a concrete instance.
     *
     * This method takes a pre-analyzed ServicePrototype and resolves it
     * into a fully instantiated and injected service instance.
     *
     * @param ServicePrototype $prototype The service prototype to resolve
     *
     * @return mixed The resolved service instance
     *
     * @throws ContainerExceptionInterface If resolution fails
     */
    public function resolve(ServicePrototype $prototype) : mixed;

    /**
     * Calls a callable with automatic dependency resolution.
     *
     * Resolves all parameters of the given callable using the container's
     * resolution capabilities.
     *
     * @param callable|string $callable   The callable to invoke
     * @param array           $parameters Explicit parameter overrides
     *
     * @return mixed The result of the callable execution
     *
     * @throws ContainerExceptionInterface If resolution or execution fails
     */
    public function call(callable|string $callable, array $parameters = []) : mixed;
}