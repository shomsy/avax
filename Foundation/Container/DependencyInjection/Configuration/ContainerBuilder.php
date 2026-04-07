<?php

declare(strict_types=1);

namespace Avax\Container\DependencyInjection\Configuration;

use Avax\Container\Container;
use Avax\Container\ContainerInterface;
use Avax\Container\ScopeManagerInterface;
use Avax\Container\DependencyInjection\Capability\Definitions\Bindings\Registrar;
use Avax\Container\DependencyInjection\Capability\Definitions\Store\DefinitionStore;
use Avax\Container\DependencyInjection\Capability\Injection\InjectDependencies;
use Avax\Container\DependencyInjection\Capability\Injection\Methods\MethodInjector;
use Avax\Container\DependencyInjection\Capability\Injection\Parameters\ResolveMethodParameters;
use Avax\Container\DependencyInjection\Capability\Injection\Properties\PropertyInjector;
use Avax\Container\DependencyInjection\Capability\Invocation\InvokeAction;
use Avax\Container\DependencyInjection\Capability\Observability\Metrics\CollectMetrics;
use Avax\Container\DependencyInjection\Capability\Observability\Timeline\ResolutionTimeline;
use Avax\Container\DependencyInjection\Capability\Policies\ContainerPolicy;
use Avax\Container\DependencyInjection\Capability\Prototypes\Analyze\PrototypeAnalyzer;
use Avax\Container\DependencyInjection\Capability\Prototypes\Analyze\ReflectionTypeAnalyzer;
use Avax\Container\DependencyInjection\Capability\Prototypes\Cache\FilePrototypeCache;
use Avax\Container\DependencyInjection\Capability\Prototypes\Factory\ServicePrototypeFactory;
use Avax\Container\DependencyInjection\Capability\Resolution\Contracts\ContainerRuntimeInterface;
use Avax\Container\DependencyInjection\Capability\Resolution\Engine\DependencyResolver;
use Avax\Container\DependencyInjection\Capability\Resolution\Engine\Instantiator;
use Avax\Container\DependencyInjection\Capability\Resolution\Engine\ResolutionEngine;
use Avax\Container\DependencyInjection\Capability\Resolution\Kernel\ContainerKernel;
use Avax\Container\DependencyInjection\Capability\Resolution\Kernel\RuntimeContainer;
use Avax\Container\DependencyInjection\Capability\Scopes\ScopeManager;
use Avax\Container\DependencyInjection\Capability\Scopes\ScopeRegistry;
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

        $kernel           = new ContainerKernel(definitions: $definitions, config: $kernelConfig);
        $container        = new Container(kernel: $kernel);
        $runtimeContainer = new RuntimeContainer(container: $container, kernel: $kernel);

        $engine->setContainer(container: $runtimeContainer);
        $injector->setContainer(container: $runtimeContainer);
        $propertyInjector->setContainer(container: $runtimeContainer);
        $invoker->setContainer(container: $runtimeContainer);

        $scopeRegistry->addSingleton(abstract: PsrContainerInterface::class, instance: $container);
        $scopeRegistry->addSingleton(abstract: ContainerInterface::class, instance: $container);
        $scopeRegistry->addSingleton(abstract: ContainerRuntimeInterface::class, instance: $runtimeContainer);
        $scopeRegistry->addSingleton(abstract: ScopeManagerInterface::class, instance: $scopeManager);
        $scopeRegistry->addSingleton(abstract: Container::class, instance: $container);
        $scopeRegistry->addSingleton(abstract: ContainerKernel::class, instance: $kernel);
        $scopeRegistry->addSingleton(abstract: ScopeManager::class, instance: $scopeManager);
        $scopeRegistry->addSingleton(abstract: DefinitionStore::class, instance: $definitions);
        $scopeRegistry->addSingleton(abstract: ScopeRegistry::class, instance: $scopeRegistry);

        $settings = new Settings(items: $containerConfig->settings);
        $registrar->instance(abstract: Settings::class, instance: $settings);
        $registrar->instance(abstract: 'config', instance: $settings);

        return $container;
    }
}
