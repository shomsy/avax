<?php

declare(strict_types=1);

namespace Avax\Container\Tests\Kernel;

use Avax\Container\Features\Actions\Instantiate\Instantiator;
use Avax\Container\Features\Actions\Resolve\DependencyResolver;
use Avax\Container\Features\Actions\Resolve\Engine;
use Avax\Container\Features\Core\Contracts\ContainerInternalInterface;
use Avax\Container\Features\Core\Exceptions\ContainerException;
use Avax\Container\Features\Define\Store\DefinitionStore;
use Avax\Container\Features\Operate\Scope\ScopeRegistry;
use Avax\Container\Features\Think\Prototype\Contracts\ServicePrototypeFactoryInterface;
use Avax\Container\Observe\Metrics\CollectMetrics;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;

/**
 * @see docs/tests/Kernel/EngineWiringTest.md#quick-summary
 */
final class EngineWiringTest extends TestCase
{
    private Engine $engine;

    public function test_double_initialization_throws() : void
    {
        /** @var ContainerInternalInterface&MockObject $container */
        $container = $this->createMock(ContainerInternalInterface::class);

        $this->engine->setContainer(container: $container);

        $this->expectException(exception: ContainerException::class);
        $this->engine->setContainer(container: $container);
    }

    protected function setUp() : void
    {
        $resolver     = new DependencyResolver;
        $instantiator = new Instantiator(
            prototypes: $this->createMock(ServicePrototypeFactoryInterface::class),
            resolver  : $resolver
        );
        $definitions  = new DefinitionStore;
        $registry     = new ScopeRegistry;
        $metrics      = new CollectMetrics;

        $this->engine = new Engine(
            resolver    : $resolver,
            instantiator: $instantiator,
            store       : $definitions,
            registry    : $registry,
            metrics     : $metrics
        );
    }
}
