<?php

declare(strict_types=1);

namespace Avax\Container\Tests\Core\Kernel;

use Avax\Container\Core\Kernel\KernelConfigFactory;
use Avax\Container\Features\Actions\Inject\InjectDependencies;
use Avax\Container\Features\Actions\Invoke\Core\InvokeAction;
use Avax\Container\Features\Actions\Resolve\Contracts\DependencyResolverInterface;
use Avax\Container\Features\Actions\Resolve\Contracts\EngineInterface;
use Avax\Container\Features\Core\Contracts\ContainerInternalInterface;
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
use PHPUnit\Framework\TestCase;

/**
 * @see docs/tests/Kernel/KernelConfigFactoryTest.md#quick-summary
 */
final class KernelConfigFactoryTest extends TestCase
{
    /**
     * @see docs/tests/Kernel/KernelConfigFactoryTest.md#method-testdebugtrueconfig
     */
    public function test_debug_true_config() : void
    {
        $scopeManager = new ScopeManager(registry: new ScopeRegistry);

        $prototypeFactory = new ServicePrototypeFactory(
            cache   : new FilePrototypeCache(directory: sys_get_temp_dir()),
            analyzer: new PrototypeAnalyzer(
                typeAnalyzer: new ReflectionTypeAnalyzer
            )
        );

        $config = (new KernelConfigFactory)->create(
            engine          : $this->createStub(originalClassName: EngineInterface::class),
            injector        : $this->createStub(originalClassName: InjectDependencies::class),
            invoker         : new InvokeAction(
                container: $this->createStub(originalClassName: ContainerInternalInterface::class),
                resolver : $this->createStub(originalClassName: DependencyResolverInterface::class)
            ),
            scopes          : $scopeManager,
            prototypeFactory: $prototypeFactory,
            timeline        : $this->createMock(ResolutionTimeline::class),
            metrics         : new CollectMetrics,
            policy          : new ContainerPolicy,
            terminator      : new TerminateContainer(manager: $scopeManager),
            debug           : true
        );

        $this->assertTrue(condition: $config->strictMode);
        $this->assertFalse(condition: $config->autoDefine);
        $this->assertTrue(condition: $config->devMode);
    }

    /**
     * @see docs/tests/Kernel/KernelConfigFactoryTest.md#method-testdebugfalseconfig
     */
    public function test_debug_false_config() : void
    {
        $scopeManager     = new ScopeManager(registry: new ScopeRegistry);
        $prototypeFactory = new ServicePrototypeFactory(
            cache   : new FilePrototypeCache(directory: sys_get_temp_dir()),
            analyzer: new PrototypeAnalyzer(
                typeAnalyzer: new ReflectionTypeAnalyzer
            )
        );

        $config = (new KernelConfigFactory)->create(
            engine          : $this->createStub(originalClassName: EngineInterface::class),
            injector        : $this->createStub(originalClassName: InjectDependencies::class),
            invoker         : new InvokeAction(
                container: $this->createStub(originalClassName: ContainerInternalInterface::class),
                resolver : $this->createStub(originalClassName: DependencyResolverInterface::class)
            ),
            scopes          : $scopeManager,
            prototypeFactory: $prototypeFactory,
            timeline        : $this->createMock(ResolutionTimeline::class),
            metrics         : new CollectMetrics,
            policy          : new ContainerPolicy,
            terminator      : new TerminateContainer(manager: $scopeManager),
            debug           : false
        );

        $this->assertFalse(condition: $config->strictMode);
        $this->assertTrue(condition: $config->autoDefine);
        $this->assertFalse(condition: $config->devMode);
    }

    /**
     * @see docs/tests/Kernel/KernelConfigFactoryTest.md#method-testoverridehonored
     */
    public function test_override_honored() : void
    {
        $scopeManager     = new ScopeManager(registry: new ScopeRegistry);
        $prototypeFactory = new ServicePrototypeFactory(
            cache   : new FilePrototypeCache(directory: sys_get_temp_dir()),
            analyzer: new PrototypeAnalyzer(
                typeAnalyzer: new ReflectionTypeAnalyzer
            )
        );

        $config = (new KernelConfigFactory)->create(
            engine          : $this->createStub(originalClassName: EngineInterface::class),
            injector        : $this->createStub(originalClassName: InjectDependencies::class),
            invoker         : new InvokeAction(
                container: $this->createStub(originalClassName: ContainerInternalInterface::class),
                resolver : $this->createStub(originalClassName: DependencyResolverInterface::class)
            ),
            scopes          : $scopeManager,
            prototypeFactory: $prototypeFactory,
            timeline        : $this->createMock(ResolutionTimeline::class),
            metrics         : new CollectMetrics,
            policy          : new ContainerPolicy,
            terminator      : new TerminateContainer(manager: $scopeManager),
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
