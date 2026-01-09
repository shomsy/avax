<?php

declare(strict_types=1);

namespace Avax\Container\Features\Core;

use Avax\Container\Container;
use Avax\Container\Features\Actions\Advanced\Policy\Security;
use Avax\Container\Features\Core\Contracts\BindingBuilder;
use Avax\Container\Features\Core\Contracts\ContextBuilder as ContextBuilderInterface;
use Avax\Container\Features\Core\Contracts\RegistryInterface;
use Avax\Container\Features\Define\Bind\Registrar;
use Avax\Container\Features\Define\Store\DefinitionStore;
use Avax\Container\Features\Define\Store\Compiler\CompilerPassInterface;
use Avax\Container\Features\Operate\Boot\ContainerBootstrapper;
use Avax\Container\Features\Operate\Config\BootstrapProfile;
use Avax\Container\Features\Operate\Scope\ScopeRegistry;
use Avax\Container\Features\Think\Analyze\PrototypeAnalyzer;
use Avax\Container\Features\Think\Analyze\ReflectionTypeAnalyzer;
use Avax\Container\Features\Think\Analyzer;
use Avax\Container\Features\Think\Cache\FilePrototypeCache;
use Avax\Container\Features\Think\Prototype\ServicePrototypeFactory;
use Avax\Container\Features\Think\Verify\VerifyPrototype;
use Avax\Container\Guard\Rules\ContainerPolicy;
use Closure;
use SensitiveParameter;
use Throwable;

/**
 * ContainerBuilder - The Architect of the Container.
 * 
 * Implements RegistryInterface to provide a fluent DSL for service registration.
 * Handles the orchestration of Bootstrapping, Analysis, and Compilation.
 */
final class ContainerBuilder implements RegistryInterface
{
    private BootstrapProfile|null $profile = null;
    private string|null $cacheDir = null;
    private bool $debug = false;
    private ContainerPolicy|null $policy = null;
    private array $compilerPasses = [];

    // Configuration State
    private DefinitionStore $definitions;
    private ScopeRegistry $registry;
    private Registrar $registrar;

    private function __construct()
    {
        $this->definitions = new DefinitionStore();
        $this->registry    = new ScopeRegistry();
        $this->registrar   = new Registrar(definitions: $this->definitions);
        $this->policy      = new ContainerPolicy();
    }

    /**
     * Create a new container builder instance with production defaults.
     *
     * Initializes a container builder with a production bootstrap profile,
     * ready for service registration and configuration.
     *
     * @return self New container builder instance
     * @see docs_md/Features/Core/ContainerBuilder.md#method-create
     */
    public static function create(): self
    {
        $builder          = new self();
        $builder->profile = BootstrapProfile::production();

        return $builder;
    }

    // --- RegistryInterface Implementation ---

    /**
     * Bind an abstract identifier to a concrete implementation.
     *
     * Registers a service binding with transient lifetime, allowing the container
     * to create new instances for each resolution.
     *
     * @param string $abstract Service identifier to bind
     * @param mixed $concrete Concrete implementation (class name, callable, or null)
     * @return BindingBuilder Builder for advanced binding configuration
     * @see docs_md/Features/Core/ContainerBuilder.md#method-bind
     */
    public function bind(string $abstract, mixed $concrete = null): BindingBuilder
    {
        return $this->registrar->bind(abstract: $abstract, concrete: $concrete);
    }

    /**
     * Bind an abstract identifier as a singleton.
     *
     * Registers a service that will return the same instance for all resolutions
     * throughout the application's lifetime.
     *
     * @param string $abstract Service identifier to bind
     * @param mixed $concrete Concrete implementation (class name, callable, or null)
     * @return BindingBuilder Builder for advanced binding configuration
     * @see docs_md/Features/Core/ContainerBuilder.md#method-singleton
     */
    public function singleton(string $abstract, mixed $concrete = null): BindingBuilder
    {
        return $this->registrar->singleton(abstract: $abstract, concrete: $concrete);
    }

    /**
     * Bind an abstract identifier with scoped lifetime.
     *
     * Registers a service that shares instances within a resolution scope
     * but creates new instances for different scopes.
     *
     * @param string $abstract Service identifier to bind
     * @param mixed $concrete Concrete implementation (class name, callable, or null)
     * @return BindingBuilder Builder for advanced binding configuration
     * @see docs_md/Features/Core/ContainerBuilder.md#method-scoped
     */
    public function scoped(string $abstract, mixed $concrete = null): BindingBuilder
    {
        return $this->registrar->scoped(abstract: $abstract, concrete: $concrete);
    }

    /**
     * Register a pre-existing instance as a singleton.
     *
     * Binds an existing object instance to be returned for all resolutions
     * of the given abstract identifier.
     *
     * @param string $abstract Service identifier for the instance
     * @param object $instance The object instance to register
     * @return void
     * @see docs_md/Features/Core/ContainerBuilder.md#method-instance
     */
    public function instance(string $abstract, object $instance): void
    {
        $this->registrar->instance(abstract: $abstract, instance: $instance);
    }

    /**
     * Extend an existing service with additional behavior.
     *
     * Adds a post-processing extension that modifies resolved service instances,
     * enabling decoration and enhancement of services.
     *
     * @param string $abstract Service identifier to extend
     * @param callable $closure Extension function that receives and returns the service instance
     * @return void
     * @see docs_md/Features/Core/ContainerBuilder.md#method-extend
     */
    public function extend(string $abstract, callable $closure): void
    {
        $this->definitions->addExtender(abstract: $abstract, extender: $closure);
    }

    /**
     * Create a contextual binding builder.
     *
     * Initiates context-aware binding configuration where different implementations
     * can be bound based on the consuming class context.
     *
     * @param string $consumer Class that will receive the contextual binding
     * @return ContextBuilderInterface Builder for contextual binding configuration
     * @see docs_md/Features/Core/ContainerBuilder.md#method-when
     */
    public function when(string $consumer): ContextBuilderInterface
    {
        return $this->registrar->when(consumer: $consumer);
    }

    /**
     * Configure security policy or access security builder.
     *
     * Either sets a new security policy or returns a security configuration builder
     * for advanced policy management.
     *
     * @param ContainerPolicy|null $policy Security policy to set, or null to get builder
     * @return self|Security Builder instance or self for chaining
     * @see docs_md/Features/Core/ContainerBuilder.md#method-security
     */
    public function security(ContainerPolicy $policy = null): self|Security
    {
        if ($policy !== null) {
            $this->policy = $policy;
            return $this;
        }

        return new Security(policy: $this->policy);
    }

    /**
     * Add a compiler pass to the build process.
     *
     * Registers a compiler pass that will process service definitions before
     * container construction, enabling build-time optimizations and validations.
     *
     * @param CompilerPassInterface $pass Compiler pass to execute during build
     * @return self Builder instance for method chaining
     * @see docs_md/Features/Core/ContainerBuilder.md#method-addCompilerPass
     */
    public function addCompilerPass(#[SensitiveParameter] CompilerPassInterface $pass): self
    {
        $this->compilerPasses[] = $pass;

        return $this;
    }

    /**
     * Configure the bootstrap profile.
     *
     * Sets the bootstrap profile that defines container initialization behavior,
     * including debug settings and optimization levels.
     *
     * @param BootstrapProfile $profile Bootstrap profile to apply
     * @return self Builder instance for method chaining
     * @see docs_md/Features/Core/ContainerBuilder.md#method-withProfile
     */
    public function withProfile(BootstrapProfile $profile): self
    {
        $this->profile = $profile;
        $this->debug   = $profile->container->debug;

        return $this;
    }

    /**
     * Enable or disable debug mode.
     *
     * Configures whether the container should operate in debug mode,
     * affecting error reporting and development features.
     *
     * @param bool $debug Whether to enable debug mode
     * @return self Builder instance for method chaining
     * @see docs_md/Features/Core/ContainerBuilder.md#method-debug
     */
    public function debug(bool $debug = true): self
    {
        $this->debug = $debug;

        return $this;
    }

    /**
     * Set the cache directory for compiled artifacts.
     *
     * Configures the directory where the container will store cached
     * prototypes and compiled definitions for performance optimization.
     *
     * @param string $dir Path to cache directory
     * @return self Builder instance for method chaining
     * @see docs_md/Features/Core/ContainerBuilder.md#method-cacheDir
     */
    public function cacheDir(string $dir): self
    {
        $this->cacheDir = $dir;

        return $this;
    }

    /**
     * Build the immutable Container.
     *
     * Finalizes the container configuration by executing compiler passes
     * and bootstrapping the runtime container with all registered services.
     *
     * @return Container The fully constructed and configured container
     * @throws \Avax\Container\Features\Core\Exceptions\ContainerException If compilation or bootstrap fails
     * @see docs_md/Features/Core/ContainerBuilder.md#method-build
     */
    public function build(): Container
    {
        // 1. Run Compiler Passes before bootstrapping the container
        foreach ($this->compilerPasses as $pass) {
            try {
                $pass->process(definitions: $this->definitions);
            } catch (Throwable $throwable) {
                // If a compiler pass fails, we should probably know about it
                throw new \Avax\Container\Features\Core\Exceptions\ContainerException(
                    message: sprintf('Compiler pass [%s] failed: %s', get_class($pass), $throwable->getMessage()),
                    previous: $throwable
                );
            }
        }

        // 2. Bootstrap the runtime from the "cooked" definitions
        $bootstrapper = new ContainerBootstrapper(
            policy: $this->policy,
            debug: $this->debug,
            cacheDir: $this->cacheDir
        );

        return $bootstrapper->bootstrap(
            definitions: $this->definitions,
            registry: $this->registry
        );
    }
}
