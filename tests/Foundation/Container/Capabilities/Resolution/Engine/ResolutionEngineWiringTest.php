<?php

declare(strict_types=1);

namespace Avax\Container\Tests\Capabilities\Resolution\Engine;

use Avax\Container\DependencyInjection\Capabilities\Definitions\Store\DefinitionStore;
use Avax\Container\DependencyInjection\Capabilities\Observability\Metrics\CollectMetrics;
use Avax\Container\DependencyInjection\Capabilities\Prototypes\Contracts\ServicePrototypeFactoryInterface;
use Avax\Container\DependencyInjection\Capabilities\Resolution\Contracts\ContainerRuntimeInterface;
use Avax\Container\DependencyInjection\Capabilities\Resolution\Engine\DependencyResolver;
use Avax\Container\DependencyInjection\Capabilities\Resolution\Engine\Instantiator;
use Avax\Container\DependencyInjection\Capabilities\Resolution\Engine\ResolutionEngine;
use Avax\Container\DependencyInjection\Capabilities\Resolution\Errors\ContainerException;
use Avax\Container\DependencyInjection\Capabilities\Scopes\ScopeRegistry;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;

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
