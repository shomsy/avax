<?php

declare(strict_types=1);

namespace Avax\Container\Features\Operate\Boot;

use Avax\Container\Container;
use Avax\Container\Core\ContainerKernel;
use Avax\Container\Core\Kernel\KernelConfig;
use Avax\Container\Features\Actions\Inject\InjectDependencies;
use Avax\Container\Features\Actions\Inject\PropertyInjector;
use Avax\Container\Features\Actions\Instantiate\Instantiator;
use Avax\Container\Features\Actions\Invoke\Core\InvokeAction;
use Avax\Container\Features\Actions\Resolve\DependencyResolver;
use Avax\Container\Features\Actions\Resolve\Engine;
use Avax\Container\Features\Define\Store\DefinitionStore;
use Avax\Container\Features\Operate\Scope\ScopeManager;
use Avax\Container\Features\Operate\Scope\ScopeRegistry;
use Avax\Container\Features\Operate\Shutdown\TerminateContainer;
use Avax\Container\Features\Think\Analyze\PrototypeAnalyzer;
use Avax\Container\Features\Think\Analyze\ReflectionTypeAnalyzer;
use Avax\Container\Features\Think\Cache\FilePrototypeCache;
use Avax\Container\Features\Think\Prototype\ServicePrototypeFactory;
use Avax\Container\Guard\Rules\ContainerPolicy;
use Avax\Container\Observe\Metrics\CollectMetrics;
use Avax\Container\Observe\Metrics\Sink\NullTelemetrySink;
use Avax\Container\Observe\Timeline\ResolutionTimeline;

/**
 * Standard implementation of the container bootstrapper.
 * Refactored to accept an existing DefinitionStore and ScopeRegistry from the Builder.
 *
 * @see docs_md/Features/Operate/Boot/ContainerBootstrapper.md#quick-summary
 */
class ContainerBootstrapper
{
    public function __construct(
        protected readonly ContainerPolicy|null $policy = null,
        protected readonly bool                 $debug = false,
        protected readonly string|null          $cacheDir = null
    ) {}

    /**
     * Bootstrap a fully-wired container runtime from definitions and scope storage.
     *
     * @see docs_md/Features/Operate/Boot/ContainerBootstrapper.md#method-bootstrap
     */
    public function bootstrap(
        DefinitionStore $definitions,
        ScopeRegistry   $registry
    ): Container {
        // 1. Initialize core infrastructure
        $scopes   = new ScopeManager(registry: $registry);
        $timeline = new ResolutionTimeline();
        $metrics  = new CollectMetrics(metrics: new NullTelemetrySink());

        // 2. Setup Think layer
        $prototypeCache   = new FilePrototypeCache(directory: $this->cacheDir ?? sys_get_temp_dir() . '/avax_prototypes');
        $typeAnalyzer     = new ReflectionTypeAnalyzer();
        $prototypeAnalyzer = new PrototypeAnalyzer(typeAnalyzer: $typeAnalyzer);

        $prototypeFactory = new ServicePrototypeFactory(
            cache: $prototypeCache,
            analyzer: $prototypeAnalyzer
        );

        // 3. Setup Actions layer
        $resolver         = new DependencyResolver();

        // New Instantiator replaces DependencyInjectionManager logic
        $instantiator = new Instantiator(
            prototypes: $prototypeFactory,
            resolver: $resolver
        );

        $engine = new Engine(
            definitions: $definitions,
            scopes: $registry,
            instantiator: $instantiator
        );

        $propertyInjector = new PropertyInjector(container: null);
        $injector = new InjectDependencies(
            servicePrototypeFactory: $prototypeFactory,
            propertyInjector: $propertyInjector,
            resolver: $resolver
        );

        $invoker = new InvokeAction(
            container: null,
            resolver: $resolver
        );

        $terminator = new TerminateContainer();

        // 4. Assemble Kernel Configuration
        $config = new KernelConfig(
            engine: $engine,
            injector: $injector,
            invoker: $invoker,
            scopes: $scopes,
            prototypeFactory: $prototypeFactory,
            metrics: $metrics,
            policy: $this->policy,
            terminator: $terminator,
            timeline: $timeline,
            autoDefine: true,
            strictMode: $this->policy !== null
        );

        // 5. Create Kernel and Container
        $kernel    = new ContainerKernel(definitions: $definitions, config: $config);
        $container = new Container(kernel: $kernel);

        // 6. Circular Wiring
        $engine->setContainer(container: $container);
        $instantiator->setContainer(container: $container);
        $injector->setContainer(container: $container);
        $propertyInjector->setContainer(container: $container);
        $invoker->setContainer(container: $container);

        // 7. Register core references
        $container->instance(abstract: \Avax\Container\Features\Core\Contracts\ContainerInterface::class, instance: $container);
        $container->instance(abstract: \Psr\Container\ContainerInterface::class, instance: $container);
        $container->instance(abstract: \Avax\Container\Features\Core\Contracts\ContainerInternalInterface::class, instance: $container);
        $container->instance(abstract: Container::class, instance: $container);
        $container->instance(abstract: DefinitionStore::class, instance: $definitions);
        $container->instance(abstract: ScopeRegistry::class, instance: $registry);

        // No longer registering DependencyInjectionManager::class as it's removed

        return $container;
    }
}
