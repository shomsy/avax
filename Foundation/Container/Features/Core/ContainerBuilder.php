<?php

declare(strict_types=1);

namespace Avax\Container\Features\Core;

use Avax\Container\Container;
use Avax\Container\Features\Actions\Advanced\Policy\Security;
use Avax\Container\Features\Core\Contracts\BindingBuilder;
use Avax\Container\Features\Core\Contracts\ContextBuilder as ContextBuilderInterface;
use Avax\Container\Features\Core\Contracts\RegistryInterface;
use Avax\Container\Features\Core\Exceptions\ContainerException;
use Avax\Container\Features\Define\Bind\Registrar;
use Avax\Container\Features\Define\Store\Compiler\CompilerPassInterface;
use Avax\Container\Features\Define\Store\DefinitionStore;

use Avax\Container\Features\Operate\Config\BootstrapProfile;
use Avax\Container\Features\Operate\Scope\ScopeRegistry;
use Avax\Container\Features\Think\Analyzer;
use Avax\Container\Guard\Rules\ContainerPolicy;
use SensitiveParameter;
use Throwable;

/**
 * ContainerBuilder - The Architect of the Container.
 *
 * Implements RegistryInterface to provide a fluent DSL for service registration.
 * Handles the orchestration of configuration, compilation, and the final construction
 * of the runtime Container instance.
 *
 * @package Avax\Container\Features\Core
 * @see docs/Features/Core/ContainerBuilder.md
 */
final class ContainerBuilder implements RegistryInterface
{
    private BootstrapProfile|null $profile        = null;
    private string|null           $cacheDir       = null;
    private bool                  $debug          = false;
    private ContainerPolicy|null  $policy         = null;
    private array                 $compilerPasses = [];

    // Configuration State
    private DefinitionStore $definitions;
    private ScopeRegistry   $registry;
    private Registrar       $registrar;

    /**
     * Private constructor to enforce factory usage.
     *
     * @see docs/Features/Core/ContainerBuilder.md#method-create
     */
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
     * @return self New container builder instance
     * @see docs/Features/Core/ContainerBuilder.md#method-create
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
     * @param string $abstract Service identifier to bind
     * @param mixed  $concrete Concrete implementation (class name, callable, or null)
     *
     * @return BindingBuilder Builder for advanced binding configuration
     * @see docs/Features/Core/ContainerBuilder.md#method-bind
     */
    public function bind(string $abstract, mixed $concrete = null): BindingBuilder
    {
        return $this->registrar->bind(abstract: $abstract, concrete: $concrete);
    }

    /**
     * Bind an abstract identifier as a singleton.
     *
     * @param string $abstract Service identifier to bind
     * @param mixed  $concrete Concrete implementation (class name, callable, or null)
     *
     * @return BindingBuilder Builder for advanced binding configuration
     * @see docs/Features/Core/ContainerBuilder.md#method-singleton
     */
    public function singleton(string $abstract, mixed $concrete = null): BindingBuilder
    {
        return $this->registrar->singleton(abstract: $abstract, concrete: $concrete);
    }

    /**
     * Bind an abstract identifier as a scoped service.
     *
     * @param string $abstract Service identifier to bind
     * @param mixed  $concrete Concrete implementation (class name, callable, or null)
     *
     * @return BindingBuilder Builder for advanced binding configuration
     * @see docs/Features/Core/ContainerBuilder.md#method-scoped
     */
    public function scoped(string $abstract, mixed $concrete = null): BindingBuilder
    {
        return $this->registrar->scoped(abstract: $abstract, concrete: $concrete);
    }

    /**
     * Register a pre-existing object instance.
     *
     * @param string $abstract Service identifier
     * @param object $instance Existing object instance
     *
     * @see docs/Features/Core/ContainerBuilder.md#method-instance
     */
    public function instance(string $abstract, object $instance): void
    {
        $this->registrar->instance(abstract: $abstract, instance: $instance);
    }

    /**
     * Extend a service definition with post-resolution logic.
     *
     * @param string   $abstract Service to extend
     * @param callable $closure  Extension logic
     *
     * @see docs/Features/Core/ContainerBuilder.md#method-extend
     */
    public function extend(string $abstract, callable $closure): void
    {
        $this->registrar->extend(abstract: $abstract, closure: $closure);
    }

    /**
     * Define contextual binding for a consumer.
     *
     * @param string $consumer Class name that receives contextual injection
     *
     * @return ContextBuilderInterface Contextual configuration builder
     * @see docs/Features/Core/ContainerBuilder.md#method-when
     */
    public function when(string $consumer): ContextBuilderInterface
    {
        return $this->registrar->when(consumer: $consumer);
    }

    /**
     * Assign tags to one or more services.
     *
     * @param string|string[] $abstracts Service identifiers.
     * @param string|string[] $tags      Tags to assign.
     *
     * @see docs/Features/Core/ContainerBuilder.md#method-tag
     */
    public function tag(string|array $abstracts, string|array $tags): void
    {
        $this->registrar->tag(abstracts: $abstracts, tags: $tags);
    }

    /**
     * Access or set the container security policy.
     *
     * @param ContainerPolicy|null $policy Policy to set
     *
     * @return self|Security Fluid builder or security configuration access
     * @see docs/Features/Core/ContainerBuilder.md#method-security
     */
    public function security(ContainerPolicy|null $policy = null): self|Security
    {
        if ($policy !== null) {
            $this->policy = $policy;
            return $this;
        }

        return new Security(policy: $this->policy);
    }

    /**
     * Register a compiler pass for the build phase.
     *
     * @param CompilerPassInterface $pass Compiler pass implementation
     *
     * @return self Fluid builder
     * @see docs/Features/Core/ContainerBuilder.md#method-addcompilerpass
     */
    public function addCompilerPass(CompilerPassInterface $pass): self
    {
        $this->compilerPasses[] = $pass;
        return $this;
    }

    /**
     * Apply a bootstrap profile.
     *
     * @param BootstrapProfile $profile Pre-configured bootstrap profile
     *
     * @return self Fluid builder
     * @see docs/Features/Core/ContainerBuilder.md#method-withprofile
     */
    public function withProfile(BootstrapProfile $profile): self
    {
        $this->profile = $profile;
        return $this;
    }

    /**
     * Set debug mode for the container.
     *
     * @param bool $debug Debug mode state
     *
     * @return self Fluid builder
     * @see docs/Features/Core/ContainerBuilder.md#method-debug
     */
    public function debug(bool $debug = true): self
    {
        $this->debug = $debug;
        return $this;
    }

    /**
     * Set the cache directory for compiled artifacts.
     *
     * @param string $dir Absolute path to cache directory
     *
     * @return self Fluid builder
     * @see docs/Features/Core/ContainerBuilder.md#method-cachedir
     */
    public function cacheDir(string $dir): self
    {
        $this->cacheDir = $dir;
        return $this;
    }

    /**
     * Assemble and build the runtime Container instance.
     *
     * This method orchestrates the final assembly, including compiler passes,
     * internal wiring of the engine, and mounting the Kernel.
     *
     * @return Container Fully constructed immutable container
     * @throws ContainerException If construction fails
     * @see docs/Features/Core/ContainerBuilder.md#method-build
     */
    public function build(): Container
    {
        try {
            // 1. Run Compiler Passes
            foreach ($this->compilerPasses as $pass) {
                $pass->process(definitions: $this->definitions);
            }

            // 2. Initialize Core Components
            $timeline = new \Avax\Container\Observe\Timeline\ResolutionTimeline();
            $metrics  = new \Avax\Container\Observe\Metrics\CollectMetrics();

            // 3. Pre-Analysis Layer
            $cache     = new \Avax\Container\Features\Think\Cache\FilePrototypeCache(directory: $this->cacheDir ?? sys_get_temp_dir());
            $analyzer  = new \Avax\Container\Features\Think\Analyze\ReflectionTypeAnalyzer();
            $inspector = new \Avax\Container\Features\Think\Analyze\PrototypeAnalyzer(typeAnalyzer: $analyzer);
            $factory   = new \Avax\Container\Features\Think\Prototype\ServicePrototypeFactory(
                cache: $cache,
                analyzer: $inspector
            );

            // 4. Action Layer (Resolver, Instantiator, Engine)
            $resolver = new \Avax\Container\Features\Actions\Resolve\DependencyResolver();

            $instantiator = new \Avax\Container\Features\Actions\Instantiate\Instantiator(
                prototypes: $factory,
                resolver: $resolver
            );
            $engine = new \Avax\Container\Features\Actions\Resolve\Engine(
                resolver: $resolver,
                instantiator: $instantiator,
                store: $this->definitions,
                registry: $this->registry,
                metrics: $metrics
            );

            // 5. Injection Layer
            $propertyInjector = new \Avax\Container\Features\Actions\Inject\PropertyInjector(
                container: null,
                typeAnalyzer: $analyzer
            );
            $injector = new \Avax\Container\Features\Actions\Inject\InjectDependencies(
                servicePrototypeFactory: $factory,
                propertyInjector: $propertyInjector,
                resolver: $resolver
            );

            // 6. Invocation Layer
            $invoker = new \Avax\Container\Features\Actions\Invoke\Core\InvokeAction(
                container: null,
                resolver: $resolver
            );

            // 7. Lifecycle Layer
            $scopeManager = new \Avax\Container\Features\Operate\Scope\ScopeManager(registry: $this->registry);
            $terminator   = new \Avax\Container\Features\Operate\Shutdown\TerminateContainer(
                manager: $scopeManager
            );

            // 8. Assemble Kernel config
            $config = new \Avax\Container\Core\Kernel\KernelConfig(
                engine: $engine,
                injector: $injector,
                invoker: $invoker,
                scopes: $scopeManager,
                prototypeFactory: $factory,
                timeline: $timeline,
                metrics: $metrics,
                policy: $this->policy,
                terminator: $terminator
            );

            $kernel    = new \Avax\Container\Core\ContainerKernel(definitions: $this->definitions, config: $config);
            $container = new \Avax\Container\Container(kernel: $kernel);

            // 9. Circular Wiring (Inject container back into components requiring the facade)
            $engine->setContainer(container: $container);
            $instantiator->setContainer(container: $container);
            $injector->setContainer(container: $container);
            $propertyInjector->setContainer(container: $container);
            $invoker->setContainer(container: $container);

            // 10. Self-References
            $this->registry->addSingleton(abstract: \Psr\Container\ContainerInterface::class, instance: $container);
            $this->registry->addSingleton(abstract: \Avax\Container\Features\Core\Contracts\ContainerInterface::class, instance: $container);
            $this->registry->addSingleton(abstract: Container::class, instance: $container);
            $this->registry->addSingleton(abstract: DefinitionStore::class, instance: $this->definitions);
            $this->registry->addSingleton(abstract: ScopeRegistry::class, instance: $this->registry);

            return $container;
        } catch (Throwable $e) {
            throw new ContainerException(
                message: "Container build failed: " . $e->getMessage(),
                code: (int)$e->getCode(),
                previous: $e
            );
        }
    }
}
