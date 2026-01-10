<?php

declare(strict_types=1);

namespace Avax\Container\Tests\Kernel;

use Avax\Container\Core\ContainerKernel;
use Avax\Container\Core\Kernel\Contracts\KernelContext;
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

    protected function setUp(): void
    {
        $this->definitions = new DefinitionStore();

        $registry = new ScopeRegistry();
        $scopes = new ScopeManager($registry);
        $timeline = new ResolutionTimeline();

        $cache = $this->createMock(PrototypeCache::class);
        $analyzer = new PrototypeAnalyzer(new ReflectionTypeAnalyzer());
        $factory = new ServicePrototypeFactory($cache, $analyzer);

        $resolver = new DependencyResolver();
        $instantiator = new Instantiator($factory, $resolver);
        $engine = new Engine($this->definitions, $registry, $instantiator);
        $injector = new InjectDependencies($factory, new PropertyInjector(null), $resolver);
        $invoker = new InvokeAction(null, $resolver);

        $this->config = new KernelConfig(
            engine: $engine,
            injector: $injector,
            invoker: $invoker,
            scopes: $scopes,
            prototypeFactory: $factory,
            timeline: $timeline,
            autoDefine: true
        );
    }

    /**
     * Verify that the kernel delegates retrieval to the runtime engine.
     *
     * @see docs_md/tests/Kernel/ContainerKernelTest.md#method-testgetdelegatestoruntime
     */
    public function testGetDelegatesToRuntime(): void
    {
        $kernel = new ContainerKernel($this->definitions, $this->config);
        $this->config->engine->setContainer($kernel);
        $this->config->injector->setContainer($kernel);

        $instance = $kernel->get(stdClass::class);
        $this->assertInstanceOf(stdClass::class, $instance);
    }

    /**
     * Verify that the kernel correctly checks for service existence in definitions and scopes.
     *
     * @see docs_md/tests/Kernel/ContainerKernelTest.md#method-testhaschecksdefinitionsandscopes
     */
    public function testHasChecksDefinitionsAndScopes(): void
    {
        $kernel = new ContainerKernel($this->definitions, $this->config);
        $this->config->engine->setContainer($kernel);

        $this->assertFalse($kernel->has('non-existent'));

        $def = new ServiceDefinition('service');
        $def->concrete = stdClass::class;
        $this->definitions->add($def);

        $this->assertTrue($kernel->has('service'));
    }
}
