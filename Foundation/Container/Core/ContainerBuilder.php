<?php

declare(strict_types=1);

namespace Avax\Container\Core;

use Avax\Container\Config\Settings;
use Avax\Container\Container;
use Avax\Container\Core\Kernel\KernelConfigFactory;
use Avax\Container\Features\Actions\Inject\InjectDependencies;
use Avax\Container\Features\Actions\Inject\PropertyInjector;
use Avax\Container\Features\Actions\Instantiate\Instantiator;
use Avax\Container\Features\Actions\Invoke\Core\InvokeAction;
use Avax\Container\Features\Actions\Resolve\DependencyResolver;
use Avax\Container\Features\Actions\Resolve\Engine;
use Avax\Container\Features\Core\Contracts\ContainerInterface;
use Avax\Container\Features\Define\Bind\Registrar;
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
use Avax\Container\Observe\Timeline\ResolutionTimeline;

/**
 * Low-level builder responsible for wiring the container kernel and core collaborators.
 *
 * @see docs/Core/ContainerBuilder.md#quick-summary
 */
final class ContainerBuilder
{
    /**
     * Assemble the container with deterministic wiring and defaults.
     *
     *
     *
     * @see docs/Core/ContainerBuilder.md#method-build
     */
    public function build(string $cacheDir, bool $debug = false) : Container
    {
        $definitions   = new DefinitionStore;
        $scopeRegistry = new ScopeRegistry;
        $registrar     = new Registrar(definitions: $definitions);

        $timeline  = new ResolutionTimeline;
        $metrics   = new CollectMetrics;
        $cache     = new FilePrototypeCache(directory: $cacheDir ?: sys_get_temp_dir());
        $analyzer  = new ReflectionTypeAnalyzer;
        $inspector = new PrototypeAnalyzer(typeAnalyzer: $analyzer);
        $factory   = new ServicePrototypeFactory(cache: $cache, analyzer: $inspector);

        $resolver = new DependencyResolver;

        $instantiator = new Instantiator(
            prototypes: $factory,
            resolver  : $resolver
        );
        $engine       = new Engine(
            resolver    : $resolver,
            instantiator: $instantiator,
            store       : $definitions,
            registry    : $scopeRegistry,
            metrics     : $metrics
        );

        $propertyInjector = new PropertyInjector(
            container   : null,
            typeAnalyzer: $analyzer
        );
        $injector         = new InjectDependencies(
            servicePrototypeFactory: $factory,
            propertyInjector       : $propertyInjector,
            resolver               : $resolver
        );

        $invoker = new InvokeAction(
            container: null,
            resolver : $resolver
        );

        $scopeManager = new ScopeManager(registry: $scopeRegistry);
        $terminator   = new TerminateContainer(
            manager: $scopeManager
        );

        $configFactory = new KernelConfigFactory;
        $config        = $configFactory->create(
            engine          : $engine,
            injector        : $injector,
            invoker         : $invoker,
            scopes          : $scopeManager,
            prototypeFactory: $factory,
            timeline        : $timeline,
            metrics         : $metrics,
            policy          : new ContainerPolicy,
            terminator      : $terminator,
            debug           : $debug
        );

        $kernel    = new ContainerKernel(definitions: $definitions, config: $config);
        $container = new Container(kernel: $kernel);

        // circular wiring
        $engine->setContainer(container: $container);
        $injector->setContainer(container: $container);
        $propertyInjector->setContainer(container: $container);
        $invoker->setContainer(container: $container);

        // base self references
        $scopeRegistry->addSingleton(abstract: \Psr\Container\ContainerInterface::class, instance: $container);
        $scopeRegistry->addSingleton(abstract: ContainerInterface::class, instance: $container);
        $scopeRegistry->addSingleton(abstract: Container::class, instance: $container);
        $scopeRegistry->addSingleton(abstract: DefinitionStore::class, instance: $definitions);
        $scopeRegistry->addSingleton(abstract: ScopeRegistry::class, instance: $scopeRegistry);

        // Bind default settings/config store
        $settings = new Settings(items: []);
        $registrar->instance(abstract: Settings::class, instance: $settings);
        $registrar->instance(abstract: 'config', instance: $settings);

        return $container;
    }
}
