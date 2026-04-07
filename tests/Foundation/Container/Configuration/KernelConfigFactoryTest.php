<?php

declare(strict_types=1);

namespace Avax\Container\Tests\Configuration;

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
use Avax\Container\DependencyInjection\Capabilities\Resolution\Engine\DependencyResolver;
use Avax\Container\DependencyInjection\Capabilities\Resolution\Engine\EngineInterface;
use Avax\Container\DependencyInjection\Capabilities\Scopes\ScopeManager;
use Avax\Container\DependencyInjection\Capabilities\Scopes\ScopeRegistry;
use Avax\Container\DependencyInjection\Configuration\KernelConfigFactory;
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
