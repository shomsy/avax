<?php

declare(strict_types=1);

namespace Avax\Container\Tests\Capabilities\Resolution\Kernel;

use Avax\Container\Capabilities\Definitions\Store\DefinitionStore;
use Avax\Container\Capabilities\Definitions\Store\ServiceDefinition;
use Avax\Container\Capabilities\Injection\InjectDependencies;
use Avax\Container\Capabilities\Injection\Properties\PropertyInjector;
use Avax\Container\Capabilities\Invocation\InvokeAction;
use Avax\Container\Capabilities\Observability\Metrics\CollectMetrics;
use Avax\Container\Capabilities\Observability\Timeline\ResolutionTimeline;
use Avax\Container\Capabilities\Prototypes\Analyze\PrototypeAnalyzer;
use Avax\Container\Capabilities\Prototypes\Analyze\ReflectionTypeAnalyzer;
use Avax\Container\Capabilities\Prototypes\Cache\PrototypeCache;
use Avax\Container\Capabilities\Prototypes\Factory\ServicePrototypeFactory;
use Avax\Container\Capabilities\Resolution\Engine\DependencyResolver;
use Avax\Container\Capabilities\Resolution\Engine\Instantiator;
use Avax\Container\Capabilities\Resolution\Engine\ResolutionEngine;
use Avax\Container\Capabilities\Scopes\ScopeManager;
use Avax\Container\Capabilities\Scopes\ScopeRegistry;
use Avax\Container\Configuration\KernelConfig;
use Avax\Container\Container;
use PHPUnit\Framework\TestCase;
use stdClass;

final class ContainerKernelTest extends TestCase
{
    private DefinitionStore $definitions;

    private KernelConfig $config;

    private ContainerKernel $kernel;

    public function test_get_delegates_to_runtime() : void
    {
        $instance = $this->kernel->get(id: stdClass::class);

        $this->assertInstanceOf(expected: stdClass::class, actual: $instance);
    }

    public function test_has_checks_definitions_and_scopes() : void
    {
        $this->assertFalse(condition: $this->kernel->has(id: 'non-existent'));

        $definition           = new ServiceDefinition(abstract: 'service');
        $definition->concrete = stdClass::class;
        $this->definitions->add(definition: $definition);

        $this->assertTrue(condition: $this->kernel->has(id: 'service'));
    }

    protected function setUp() : void
    {
        $this->definitions = new DefinitionStore;

        $registry = new ScopeRegistry;
        $scopes   = new ScopeManager(registry: $registry);
        $timeline = new ResolutionTimeline;

        $cache    = $this->createMock(PrototypeCache::class);
        $analyzer = new PrototypeAnalyzer(typeAnalyzer: new ReflectionTypeAnalyzer);
        $factory  = new ServicePrototypeFactory(cache: $cache, analyzer: $analyzer);

        $resolver         = new DependencyResolver;
        $instantiator     = new Instantiator(prototypes: $factory, resolver: $resolver);
        $engine           = new ResolutionEngine(
            resolver    : $resolver,
            instantiator: $instantiator,
            store       : $this->definitions,
            registry    : $registry,
            metrics     : new CollectMetrics
        );
        $propertyInjector = new PropertyInjector(container: null);
        $injector         = new InjectDependencies(
            servicePrototypeFactory: $factory,
            propertyInjector       : $propertyInjector,
            resolver               : $resolver
        );
        $invoker          = new InvokeAction(container: null, resolver: $resolver);

        $this->config = new KernelConfig(
            engine          : $engine,
            injector        : $injector,
            invoker         : $invoker,
            scopes          : $scopes,
            prototypeFactory: $factory,
            timeline        : $timeline,
            autoDefine      : true
        );

        $this->kernel = new ContainerKernel(definitions: $this->definitions, config: $this->config);
        $container    = new Container(kernel: $this->kernel);

        $engine->setContainer(container: $container);
        $injector->setContainer(container: $container);
        $propertyInjector->setContainer(container: $container);
        $invoker->setContainer(container: $container);
    }
}
