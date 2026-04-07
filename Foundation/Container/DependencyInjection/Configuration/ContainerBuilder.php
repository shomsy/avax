<?php

declare(strict_types=1);

namespace Avax\Container\DependencyInjection\Configuration;

use Avax\Container\Container;
use Avax\Container\ContainerInterface;
use Avax\Container\DependencyInjection\Capabilities\Definitions\Bindings\Registrar;
use Avax\Container\DependencyInjection\Capabilities\Definitions\Store\DefinitionStore;
use Avax\Container\DependencyInjection\Capabilities\Injection\InjectDependencies;
use Avax\Container\DependencyInjection\Capabilities\Injection\Methods\MethodInjector;
use Avax\Container\DependencyInjection\Capabilities\Injection\Parameters\ResolveMethodParameters;
use Avax\Container\DependencyInjection\Capabilities\Injection\Properties\PropertyInjector;
use Avax\Container\DependencyInjection\Capabilities\Invocation\InvokeAction;
use Avax\Container\DependencyInjection\Capabilities\Observability\Metrics\CollectMetrics;
use Avax\Container\DependencyInjection\Capabilities\Observability\Timeline\ResolutionTimeline;
use Avax\Container\DependencyInjection\Capabilities\Policies\ContainerPolicy;
use Avax\Container\DependencyInjection\Capabilities\Prototypes\Analyze\PrototypeAnalyzer;
use Avax\Container\DependencyInjection\Capabilities\Prototypes\Analyze\ReflectionTypeAnalyzer;
use Avax\Container\DependencyInjection\Capabilities\Prototypes\Cache\FilePrototypeCache;
use Avax\Container\DependencyInjection\Capabilities\Prototypes\Factory\ServicePrototypeFactory;
use Avax\Container\DependencyInjection\Capabilities\Resolution\Contracts\ContainerRuntimeInterface;
use Avax\Container\DependencyInjection\Capabilities\Resolution\Engine\DependencyResolver;
use Avax\Container\DependencyInjection\Capabilities\Resolution\Engine\Instantiator;
use Avax\Container\DependencyInjection\Capabilities\Resolution\Engine\ResolutionEngine;
use Avax\Container\DependencyInjection\Capabilities\Resolution\Kernel\ContainerKernel;
use Avax\Container\DependencyInjection\Capabilities\Scopes\ScopeManager;
use Avax\Container\DependencyInjection\Capabilities\Scopes\ScopeRegistry;
use Psr\Container\ContainerInterface as PsrContainerInterface;

/**
 * Assembly root for the container runtime.
 */
final class ContainerBuilder
{
    /**
     * @param array<string, mixed> $settings
     */
    public function build(
        string $cacheDir = '',
        bool $debug = false,
        array $settings = [],
        ContainerConfig|null $config = null
    ) : Container
    {
        $containerConfig = $config ?? new ContainerConfig(
            cacheDir: $cacheDir,
            debug   : $debug,
            settings: $settings
        );
        $definitions   = new DefinitionStore;
        $scopeRegistry = new ScopeRegistry;
        $registrar     = new Registrar(definitions: $definitions);

        $timeline  = new ResolutionTimeline;
        $metrics   = new CollectMetrics;
        $cache     = new FilePrototypeCache(directory: $containerConfig->cacheDirectory());
        $analyzer  = new ReflectionTypeAnalyzer;
        $inspector = new PrototypeAnalyzer(typeAnalyzer: $analyzer);
        $factory   = new ServicePrototypeFactory(cache: $cache, analyzer: $inspector);

        $resolver = new DependencyResolver;

        $instantiator = new Instantiator(
            prototypes: $factory,
            resolver  : $resolver
        );
        $engine       = new ResolutionEngine(
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
        $methodInjector   = new MethodInjector(
            parameterResolver: new ResolveMethodParameters(resolver: $resolver)
        );
        $injector         = new InjectDependencies(
            servicePrototypeFactory: $factory,
            propertyInjector       : $propertyInjector,
            methodInjector         : $methodInjector
        );

        $invoker = new InvokeAction(
            container: null,
            resolver : $resolver
        );

        $scopeManager = new ScopeManager(registry: $scopeRegistry);
        $kernelConfig = (new KernelConfigFactory)->create(
            engine          : $engine,
            injector        : $injector,
            invoker         : $invoker,
            scopes          : $scopeManager,
            prototypeFactory: $factory,
            timeline        : $timeline,
            metrics         : $metrics,
            policy          : new ContainerPolicy,
            debug           : $containerConfig->debug
        );

        $kernel    = new ContainerKernel(definitions: $definitions, config: $kernelConfig);
        $container = new Container(kernel: $kernel);

        $engine->setContainer(container: $container);
        $injector->setContainer(container: $container);
        $propertyInjector->setContainer(container: $container);
        $invoker->setContainer(container: $container);

        $scopeRegistry->addSingleton(abstract: PsrContainerInterface::class, instance: $container);
        $scopeRegistry->addSingleton(abstract: ContainerInterface::class, instance: $container);
        $scopeRegistry->addSingleton(abstract: ContainerRuntimeInterface::class, instance: $container);
        $scopeRegistry->addSingleton(abstract: Container::class, instance: $container);
        $scopeRegistry->addSingleton(abstract: ContainerKernel::class, instance: $kernel);
        $scopeRegistry->addSingleton(abstract: DefinitionStore::class, instance: $definitions);
        $scopeRegistry->addSingleton(abstract: ScopeRegistry::class, instance: $scopeRegistry);

        $settings = new Settings(items: $containerConfig->settings);
        $registrar->instance(abstract: Settings::class, instance: $settings);
        $registrar->instance(abstract: 'config', instance: $settings);

        return $container;
    }
}
