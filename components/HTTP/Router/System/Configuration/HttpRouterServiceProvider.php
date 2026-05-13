<?php

declare(strict_types=1);

namespace Avax\Components\HTTP\Router\System\Configuration;

use Avax\Components\Application\Container\System\Capabilities\ResolveCallable\ResolveCallable;
use Avax\Components\Application\Container\System\Capabilities\ServiceProvider\ServiceProvider;
use Avax\Components\Application\Container\System\ContainerInterface;
use Avax\Components\HTTP\Dispatcher\System\Capabilities\ActionResolution\ControllerResolver;
use Avax\Components\HTTP\Router\System\Capabilities\RouteCollection\RouteCollection;
use Avax\Components\HTTP\Router\System\Flows\MatchRoute\MatchRoute;
use Avax\Components\HTTP\Router\System\PublicSurface\Router;

/**
 * HttpRouterServiceProvider — registers HTTP routing and dispatching dependencies.
 */
final class HttpRouterServiceProvider implements ServiceProvider
{
    public function register(ContainerInterface $container) : void
    {
        // Route collection — shared across the router lifecycle
        $container->singleton(RouteCollection::class, static fn () : RouteCollection => new RouteCollection(),
        );

        // Route matcher — stateless, can be shared
        $container->singleton(MatchRoute::class, static fn () : MatchRoute => new MatchRoute(),
        );

        // ResolveCallable — optional PSR-11 container integration for callable resolution
        $container->singleton(ResolveCallable::class, static fn (ContainerInterface $c) : ResolveCallable => new ResolveCallable(container: $c),
        );

        // Router — uses injected dependencies, resolves middleware through ResolveCallable
        $container->singleton(Router::class, static fn (ContainerInterface $c) : Router => new Router(
            callableResolver: $c->get(ResolveCallable::class),
            routeCollection : $c->get(RouteCollection::class),
            matchRoute      : $c->get(MatchRoute::class),
        ),
        );

        // ControllerResolver — delegates to ResolveCallable, never uses new $className()
        $container->singleton(ControllerResolver::class, static fn (ContainerInterface $c) : ControllerResolver => new ControllerResolver(
            resolver: $c->get(ResolveCallable::class),
        ),
        );
    }

    public function boot(ContainerInterface $container) : void
    {
        // No boot-time logic needed for router component
    }
}
