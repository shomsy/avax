<?php

declare(strict_types=1);

namespace components\Container\Tests\Capability\Resolution\Engine;

use components\Container\DependencyInjection\Capability\Definitions\Store\DefinitionStore;
use components\Container\DependencyInjection\Capability\Observability\Metrics\CollectMetrics;
use components\Container\DependencyInjection\Capability\Prototypes\Contracts\ServicePrototypeFactoryInterface;
use components\Container\DependencyInjection\Capability\Resolution\Contracts\ContainerRuntimeInterface;
use components\Container\DependencyInjection\Capability\Resolution\Engine\DependencyResolver;
use components\Container\DependencyInjection\Capability\Resolution\Engine\Instantiator;
use components\Container\DependencyInjection\Capability\Resolution\Engine\ResolutionEngine;
use components\Container\DependencyInjection\Capability\Scopes\ScopeRegistry;
use components\Container\DI\Capabilities\Diagnostics\Errors\ContainerException;
use components\Tests\TestCase;
use Override;
use PHPUnit\Framework\MockObject\MockObject;

final class ResolutionEngineWiringTest extends TestCase
{
    private ResolutionEngine $engine;

    public function test_double_initialization_throws() : void
    {
        /** @var ContainerRuntimeInterface&MockObject $container */
        $container = $this->createMock(ContainerRuntimeInterface::class);

        $this->engine->setContainer(container: $container);

        $this->expectException(exception: ContainerException::class);
        $this->engine->setContainer(container: $container);
    }

    #[Override]
    protected function setUp() : void
    {
        $resolver     = new DependencyResolver;
        $instantiator = new Instantiator(
            prototypes: $this->createMock(ServicePrototypeFactoryInterface::class),
            resolver  : $resolver
        );

        $this->engine = new ResolutionEngine(
            resolver    : $resolver,
            instantiator: $instantiator,
            store       : new DefinitionStore,
            registry    : new ScopeRegistry,
            metrics     : new CollectMetrics
        );
    }
}
