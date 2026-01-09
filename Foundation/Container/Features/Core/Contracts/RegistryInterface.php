<?php

declare(strict_types=1);

namespace Avax\Container\Features\Core\Contracts;

/**
 * RegistryInterface - Contract for service registration and configuration.
 * Used primarily by the ContainerBuilder or Configuration flows.
 */
interface RegistryInterface
{
    /**
     * Register a binding with the container.
     */
    public function bind(string $abstract, mixed $concrete = null): BindingBuilder;

    /**
     * Register a shared (singleton) binding with the container.
     */
    public function singleton(string $abstract, mixed $concrete = null): BindingBuilder;

    /**
     * Register a scoped binding with the container.
     */
    public function scoped(string $abstract, mixed $concrete = null): BindingBuilder;

    /**
     * Register an existing instance as a shared service.
     */
    public function instance(string $abstract, object $instance): void;

    /**
     * Register an extender for a service.
     */
    public function extend(string $abstract, callable $closure): void;

    /**
     * Define contextual binding for a consumer.
     */
    public function when(string $consumer): ContextBuilder;
}
