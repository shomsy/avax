<?php

declare(strict_types=1);

namespace Avax\Container\Tests\Configuration;

use Avax\Container\Capabilities\Injection\InjectDependencies;
use Avax\Container\Capabilities\Injection\Methods\MethodInjector;
use Avax\Container\Capabilities\Injection\Parameters\ResolveMethodParameters;
use Avax\Container\Capabilities\Injection\Properties\PropertyInjector;
use Avax\Container\Capabilities\Invocation\InvokeAction;
use Avax\Container\Capabilities\Observability\Metrics\CollectMetrics;
use Avax\Container\Capabilities\Observability\Timeline\ResolutionTimeline;
use Avax\Container\Capabilities\Policies\ContainerPolicy;
use Avax\Container\Capabilities\Prototypes\Analyze\PrototypeAnalyzer;
use Avax\Container\Capabilities\Prototypes\Analyze\ReflectionTypeAnalyzer;
use Avax\Container\Capabilities\Prototypes\Cache\FilePrototypeCache;
use Avax\Container\Capabilities\Prototypes\Factory\ServicePrototypeFactory;
use Avax\Container\Capabilities\Resolution\Engine\DependencyResolver;
use Avax\Container\Capabilities\Resolution\Engine\EngineInterface;
use Avax\Container\Capabilities\Scopes\ScopeManager;
use Avax\Container\Capabilities\Scopes\ScopeRegistry;
use Avax\Container\Configuration\KernelConfigFactory;
use PHPUnit\Framework\TestCase;

final class KernelConfigFactoryTest extends TestCase
{
    public function test_debug_true_config() : void
    {
        $config = $this->makeFactory()->create(
            engine          : $this->createStub(EngineInterface::class),
            injector        : $this->makeInjector(),
            invoker         : $this->makeInvoker(),
            scopes          : new ScopeManager(registry: new ScopeRegistry),
            prototypeFactory: $this->makePrototypeFactory(),
            timeline        : $this->createMock(ResolutionTimeline::class),
            metrics         : new CollectMetrics,
            policy          : new ContainerPolicy,
            debug           : true
        );

        $this->assertTrue(condition: $config->strictMode);
        $this->assertFalse(condition: $config->autoDefine);
        $this->assertTrue(condition: $config->devMode);
    }

    public function test_debug_false_config() : void
    {
        $config = $this->makeFactory()->create(
            engine          : $this->createStub(EngineInterface::class),
            injector        : $this->makeInjector(),
            invoker         : $this->makeInvoker(),
            scopes          : new ScopeManager(registry: new ScopeRegistry),
            prototypeFactory: $this->makePrototypeFactory(),
            timeline        : $this->createMock(ResolutionTimeline::class),
            metrics         : new CollectMetrics,
            policy          : new ContainerPolicy,
            debug           : false
        );

        $this->assertFalse(condition: $config->strictMode);
        $this->assertTrue(condition: $config->autoDefine);
        $this->assertFalse(condition: $config->devMode);
    }

    public function test_override_honored() : void
    {
        $config = $this->makeFactory()->create(
            engine          : $this->createStub(EngineInterface::class),
            injector        : $this->makeInjector(),
            invoker         : $this->makeInvoker(),
            scopes          : new ScopeManager(registry: new ScopeRegistry),
            prototypeFactory: $this->makePrototypeFactory(),
            timeline        : $this->createMock(ResolutionTimeline::class),
            metrics         : new CollectMetrics,
            policy          : new ContainerPolicy,
            debug           : false,
            strictMode      : true,
            autoDefine      : false,
            devMode         : true
        );

        $this->assertTrue(condition: $config->strictMode);
        $this->assertFalse(condition: $config->autoDefine);
        $this->assertTrue(condition: $config->devMode);
    }

    private function makeFactory() : KernelConfigFactory
    {
        return new KernelConfigFactory;
    }

    private function makePrototypeFactory() : ServicePrototypeFactory
    {
        return new ServicePrototypeFactory(
            cache   : new FilePrototypeCache(directory: sys_get_temp_dir()),
            analyzer: new PrototypeAnalyzer(typeAnalyzer: new ReflectionTypeAnalyzer)
        );
    }

    private function makeInjector() : InjectDependencies
    {
        $resolver = new DependencyResolver;

        return new InjectDependencies(
            servicePrototypeFactory: $this->makePrototypeFactory(),
            propertyInjector       : new PropertyInjector(container: null),
            methodInjector         : new MethodInjector(
                parameterResolver: new ResolveMethodParameters(resolver: $resolver)
            )
        );
    }

    private function makeInvoker() : InvokeAction
    {
        return new InvokeAction(container: null, resolver: new DependencyResolver);
    }
}
