<?php

declare(strict_types=1);

namespace Avax\Container\Tests\Kernel;

use Avax\Container\Core\ContainerKernel;
use Avax\Container\Core\Kernel\KernelConfig;
use Avax\Container\Features\Actions\Inject\InjectDependencies;
use Avax\Container\Features\Actions\Inject\PropertyInjector;
use Avax\Container\Features\Actions\Instantiate\Instantiator;
use Avax\Container\Features\Actions\Invoke\Core\InvokeAction;
use Avax\Container\Features\Actions\Resolve\DependencyResolver;
use Avax\Container\Features\Actions\Resolve\Engine;
use Avax\Container\Features\Define\Store\DefinitionStore;
use Avax\Container\Features\Define\Store\ServiceDefinition;
use Avax\Container\Features\Operate\Scope\ScopeManager;
use Avax\Container\Features\Operate\Scope\ScopeRegistry;
use Avax\Container\Features\Think\Analyze\PrototypeAnalyzer;
use Avax\Container\Features\Think\Analyze\ReflectionTypeAnalyzer;
use Avax\Container\Features\Think\Cache\PrototypeCache;
use Avax\Container\Features\Think\Prototype\ServicePrototypeFactory;
use Avax\Container\Observe\Metrics\CollectMetrics;
use Avax\Container\Observe\Timeline\ResolutionTimeline;
use PHPUnit\Framework\TestCase;
use stdClass;

/**
 * PHPUnit test coverage for Container component behavior.
 *
 * @see docs_md/tests/Kernel/ContainerKernelTest.md#quick-summary
 */
final class ContainerKernelTest extends TestCase
{
    private DefinitionStore $definitions;

    private KernelConfig $config;

    /**
     * Verify that the kernel delegates retrieval to the runtime engine.
     *
     * @see docs_md/tests/Kernel/ContainerKernelTest.md#method-testgetdelegatestoruntime
     */
    public function test_get_delegates_to_runtime() : void
    {
        $kernel = new ContainerKernel(definitions: $this->definitions, config: $this->config);
        $this->config->engine->setContainer(container: $kernel);
        $this->config->injector->setContainer(container: $kernel);

        $instance = $kernel->get(id: stdClass::class);
        $this->assertInstanceOf(expected: stdClass::class, actual: $instance);
    }

    /**
     * Verify that the kernel correctly checks for service existence in definitions and scopes.
     *
     * @see docs_md/tests/Kernel/ContainerKernelTest.md#method-testhaschecksdefinitionsandscopes
     */
    public function test_has_checks_definitions_and_scopes() : void
    {
        $kernel = new ContainerKernel(definitions: $this->definitions, config: $this->config);
        $this->config->engine->setContainer(container: $kernel);

        $this->assertFalse(condition: $kernel->has(id: 'non-existent'));

        $def           = new ServiceDefinition(abstract: 'service');
        $def->concrete = stdClass::class;
        $this->definitions->add(definition: $def);

        $this->assertTrue(condition: $kernel->has(id: 'service'));
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

        $resolver     = new DependencyResolver;
        $instantiator = new Instantiator(prototypes: $factory, resolver: $resolver);
        $engine       = new Engine(
            resolver    : $resolver,
            instantiator: $instantiator,
            store       : $this->definitions,
            registry    : $registry,
            metrics     : new CollectMetrics
        );
        $injector     = new InjectDependencies(servicePrototypeFactory: $factory, propertyInjector: new PropertyInjector(container: null), resolver: $resolver);
        $invoker      = new InvokeAction(container: null, resolver: $resolver);

        $this->config = new KernelConfig(
            engine          : $engine,
            injector        : $injector,
            invoker         : $invoker,
            scopes          : $scopes,
            prototypeFactory: $factory,
            timeline        : $timeline,
            autoDefine      : true
        );
    }
}
