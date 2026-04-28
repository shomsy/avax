<?php

declare(strict_types=1);

namespace Avax\Components\Application\Container\DI\Capabilities\Declaration\Bindings;

/**
 * Shared registration contract for the container write side.
 */
interface ServiceRegistryInterface
{
    /**
     * Registers one alias for one canonical service id.
     */
    public function alias(string $alias, string $abstract) : void;

    /**
     * Registers one transient service.
     */
    public function bind(string $abstract, mixed $concrete = null) : ServiceRegistration;

    /**
     * Registers one deferred transient service.
     */
    public function defer(string $abstract, mixed $concrete = null) : ServiceRegistration;

    /**
     * Registers one shared service.
     */
    public function singleton(string $abstract, mixed $concrete = null) : ServiceRegistration;

    /**
     * Registers one scoped service.
     */
    public function scoped(string $abstract, mixed $concrete = null) : ServiceRegistration;

    /**
     * Registers one prebuilt instance.
     */
    public function instance(string $abstract, object $instance) : void;

    /**
     * Appends one post-build extender.
     */
    public function extend(string $abstract, callable $closure) : void;

    /**
     * Appends one explicit decorator to one service id.
     */
    public function decorate(string $abstract, callable|DecoratorInterface|string $decorator) : void;

    /**
     * Starts one target-specific registration override.
     */
    public function when(string $consumer) : RegisterForTarget;

    /**
     * Tags one or more services.
     */
    public function tag(string|array $abstracts, string|array $tags) : void;
}
