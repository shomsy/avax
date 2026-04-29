<?php

declare(strict_types=1);

namespace Avax\Tests\Foundation\Container\Configuration;

use Avax\Tests\TestCase;
use components\Container\DependencyInjection\Capability\Injection\InjectDependencies;
use components\Container\DependencyInjection\Capability\Injection\Methods\MethodInjector;
use components\Container\DependencyInjection\Capability\Injection\Parameters\ResolveMethodParameters;
use components\Container\DependencyInjection\Capability\Injection\Properties\PropertyInjector;
use components\Container\DependencyInjection\Capability\Invocation\InvokeAction;
use components\Container\DependencyInjection\Capability\Observability\Metrics\CollectMetrics;
use components\Container\DependencyInjection\Capability\Observability\Timeline\ResolutionTimeline;
use components\Container\DependencyInjection\Capability\Policies\ContainerPolicy;
use components\Container\DependencyInjection\Capability\Prototypes\Analyze\PrototypeAnalyzer;
use components\Container\DependencyInjection\Capability\Prototypes\Analyze\ReflectionTypeAnalyzer;
use components\Container\DependencyInjection\Capability\Prototypes\Cache\FilePrototypeCache;
use components\Container\DependencyInjection\Capability\Prototypes\Factory\ServicePrototypeFactory;
use components\Container\DependencyInjection\Capability\Resolution\Engine\DependencyResolver;
use components\Container\DependencyInjection\Capability\Resolution\Engine\EngineInterface;
use components\Container\DependencyInjection\Capability\Scopes\ScopeManager;
use components\Container\DependencyInjection\Capability\Scopes\ScopeRegistry;
use components\Container\DependencyInjection\Configuration\KernelConfigFactory;

final class KernelConfigFactoryTest extends TestCase
{
    public function test_debug_true_config() : void
    {
        $config = $this->makeFactory()->create(
            engine          : $this->createStub(originalClassName: EngineInterface::class),
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

    private function makeFactory() : KernelConfigFactory
    {
        return new KernelConfigFactory;
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

    private function makePrototypeFactory() : ServicePrototypeFactory
    {
        return new ServicePrototypeFactory(
            cache   : new FilePrototypeCache(directory: sys_get_temp_dir()),
            analyzer: new PrototypeAnalyzer(typeAnalyzer: new ReflectionTypeAnalyzer)
        );
    }

    private function makeInvoker() : InvokeAction
    {
        return new InvokeAction(container: null, resolver: new DependencyResolver);
    }

    public function test_debug_false_config() : void
    {
        $config = $this->makeFactory()->create(
            engine          : $this->createStub(originalClassName: EngineInterface::class),
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
            engine          : $this->createStub(originalClassName: EngineInterface::class),
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
}
