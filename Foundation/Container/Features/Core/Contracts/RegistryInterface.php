<?php

declare(strict_types=1);

namespace Avax\Container\Features\Core\Contracts;

use Avax\Container\Features\Define\Store\Compiler\CompilerPassInterface;
use Avax\Container\Features\Operate\Config\BootstrapProfile;
use Avax\Container\Guard\Rules\ContainerPolicy;

/**
 * Service Registry Interface
 *
 * Defines the contract for service registration and container configuration.
 * Provides a fluent DSL for binding services, configuring lifecycles, and
 * setting up contextual dependencies.
 *
 * @see     docs/Features/Core/Contracts/RegistryInterface.md
 */
interface RegistryInterface
{
    /**
     * Bind an abstract identifier to a concrete implementation.
     *
     * @param string $abstract Service identifier to bind
     * @param mixed  $concrete Concrete implementation (class name, callable, or null)
     *
     * @return BindingBuilder Builder for advanced binding configuration
     *
     * @see docs/Features/Core/Contracts/RegistryInterface.md#method-bind
     */
    public function bind(string $abstract, mixed $concrete = null) : BindingBuilder;

    /**
     * Bind an abstract identifier as a singleton.
     *
     * @param string $abstract Service identifier to bind
     * @param mixed  $concrete Concrete implementation (class name, callable, or null)
     *
     * @return BindingBuilder Builder for advanced binding configuration
     *
     * @see docs/Features/Core/Contracts/RegistryInterface.md#method-singleton
     */
    public function singleton(string $abstract, mixed $concrete = null) : BindingBuilder;

    /**
     * Bind an abstract identifier as a scoped service.
     *
     * @param string $abstract Service identifier to bind
     * @param mixed  $concrete Concrete implementation (class name, callable, or null)
     *
     * @return BindingBuilder Builder for advanced binding configuration
     *
     * @see docs/Features/Core/Contracts/RegistryInterface.md#method-scoped
     */
    public function scoped(string $abstract, mixed $concrete = null) : BindingBuilder;

    /**
     * Register a pre-existing object instance.
     *
     * @param string $abstract Service identifier
     * @param object $instance Existing object instance
     *
     * @see docs/Features/Core/Contracts/RegistryInterface.md#method-instance
     */
    public function instance(string $abstract, object $instance) : void;

    /**
     * Extend a service definition with post-resolution logic.
     *
     * @param string   $abstract Service to extend
     * @param callable $closure  Extension logic
     *
     * @see docs/Features/Core/Contracts/RegistryInterface.md#method-extend
     */
    public function extend(string $abstract, callable $closure) : void;

    /**
     * Define contextual binding for a consumer.
     *
     * @param string $consumer Class name that receives contextual injection
     *
     * @return ContextBuilder Contextual configuration builder
     *
     * @see docs/Features/Core/Contracts/RegistryInterface.md#method-when
     */
    public function when(string $consumer) : ContextBuilder;

    /**
     * Assign tags to one or more services.
     *
     * @param string|string[] $abstracts Service identifiers
     * @param string|string[] $tags      Tags to assign
     *
     * @see docs/Features/Core/Contracts/RegistryInterface.md#method-tag
     */
    public function tag(string|array $abstracts, string|array $tags) : void;

    /**
     * Access or set the container security policy.
     *
     * @param ContainerPolicy|null $policy Policy to set
     *
     * @return mixed Fluid builder or security configuration access
     *
     * @see docs/Features/Core/Contracts/RegistryInterface.md#method-security
     */
    public function security(ContainerPolicy|null $policy = null) : mixed;

    /**
     * Register a compiler pass for the build phase.
     *
     * @param CompilerPassInterface $pass Compiler pass implementation
     *
     * @return self Fluid builder
     *
     * @see docs/Features/Core/Contracts/RegistryInterface.md#method-addcompilerpass
     */
    public function addCompilerPass(CompilerPassInterface $pass) : self;

    /**
     * Apply a bootstrap profile.
     *
     * @param BootstrapProfile $profile Pre-configured bootstrap profile
     *
     * @return self Fluid builder
     *
     * @see docs/Features/Core/Contracts/RegistryInterface.md#method-withprofile
     */
    public function withProfile(BootstrapProfile $profile) : self;

    /**
     * Set debug mode for the container.
     *
     * @param bool $debug Debug mode state
     *
     * @return self Fluid builder
     *
     * @see docs/Features/Core/Contracts/RegistryInterface.md#method-debug
     */
    public function debug(bool $debug = true) : self;

    /**
     * Set the cache directory for compiled artifacts.
     *
     * @param string $dir Absolute path to cache directory
     *
     * @return self Fluid builder
     *
     * @see docs/Features/Core/Contracts/RegistryInterface.md#method-cachedir
     */
    public function cacheDir(string $dir) : self;
}
