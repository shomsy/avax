<?php

declare(strict_types=1);

namespace Avax\Tests\Unit\Components\HTTP\Dispatcher\Configuration;

use Avax\Components\Application\Container\System\Capabilities\ResolveCallable\ResolveCallable;
use Avax\Components\Application\Container\System\Foundation\SimpleContainer;
use Avax\Components\HTTP\Dispatcher\System\Capabilities\ActionResolution\ControllerResolver;
use Avax\Components\HTTP\Dispatcher\System\Capabilities\ArgumentResolution\ArgumentResolver;
use Avax\Components\HTTP\Dispatcher\System\Configuration\DispatcherServiceProvider;
use Avax\Components\HTTP\Dispatcher\System\Flows\DispatchRouteAction\DispatchRouteAction;
use Avax\Components\HTTP\Dispatcher\System\PublicSurface\ControllerDispatcher;
use Avax\Components\HTTP\Response\System\Capabilities\CreateHttpResponse\CreateHttpResponse;
use Avax\Components\HTTP\SecureRequest\System\Capabilities\ResolveSecureRequest\SecureRequestInputBuilder;
use Psr\Container\ContainerInterface as PsrContainerInterface;
use PHPUnit\Framework\TestCase;

final class DispatcherServiceProviderTest extends TestCase
{
    private SimpleContainer $container;
    private DispatcherServiceProvider $provider;

    protected function setUp(): void
    {
        $this->container = new SimpleContainer();

        // Register cross-component dependencies required by DispatcherServiceProvider
        $this->container->singleton(ResolveCallable::class, static fn () => new ResolveCallable());
        $this->container->singleton(PsrContainerInterface::class, static fn (SimpleContainer $c) => $c);
        $this->container->singleton(SecureRequestInputBuilder::class, static fn () => new SecureRequestInputBuilder());
        $this->container->singleton(CreateHttpResponse::class, static fn () => new CreateHttpResponse());

        $this->provider = new DispatcherServiceProvider();
        $this->provider->register($this->container);
        $this->provider->boot($this->container);
    }

    public function test_controller_resolver_resolves(): void
    {
        $resolver = $this->container->get(ControllerResolver::class);

        $this->assertInstanceOf(ControllerResolver::class, $resolver);
    }

    public function test_argument_resolver_resolves(): void
    {
        $resolver = $this->container->get(ArgumentResolver::class);

        $this->assertInstanceOf(ArgumentResolver::class, $resolver);
    }

    public function test_dispatch_route_action_resolves(): void
    {
        $flow = $this->container->get(DispatchRouteAction::class);

        $this->assertInstanceOf(DispatchRouteAction::class, $flow);
    }

    public function test_controller_dispatcher_resolves(): void
    {
        $dispatcher = $this->container->get(ControllerDispatcher::class);

        $this->assertInstanceOf(ControllerDispatcher::class, $dispatcher);
    }
}
