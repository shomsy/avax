<?php
declare(strict_types=1);

namespace Avax\Container\Contracts;

/**
 * Interface defining the contract for a dependency injection container.
 *
 * Extends the PSR-11 ContainerInterface to provide additional methods for
 * registering, bootstrapping, and binding services. This interface is
 * essential for the application's dependency management and lifecycle.
 */
interface ContainerInterface extends \Psr\Container\ContainerInterface
{
    /**
     * Register any application services.
     *
     * This method is called once during the application's bootstrapping process.
     * Use this method to bind any essential services or classes to the container.
     */
    public function register() : void;

    /**
     * Bootstrap any application services.
     *
     * Called after service providers are registered. It is meant for initializing
     * middleware, event listeners, and deferred services before handling requests.
     */
    public function boot() : void;

    /**
     * Bind a service to the container.
     *
     * This method binds a concrete implementation or a closure to an abstract class
     * or interface. This allows the container to resolve it when requested,
     * facilitating dependency injection.
     *
     * @param string $abstract         The abstract name or interface.
     * @param callable|string $concrete The concrete implementation or closure.
     */
    public function bind(string $abstract, callable|string $concrete) : void;

    /**
     * Retrieve a service from the container.
     *
     * This method will fetch the service associated with the given identifier.
     *
     * @param string $id The identifier of the service.
     * @return mixed The instance of the service, or throws an exception if the id is not known to the container.
     */
    public function get(string $id): mixed;

    /**
     * Check if the container has a service for the given identifier.
     *
     * Returns true if the container can return an entry for the given identifier,
     * false otherwise.
     *
     * @param string $id The identifier of the service.
     * @return bool Whether the service exists in the container.
     */
    public function has(string $id): bool;
}