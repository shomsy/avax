<?php

declare(strict_types=1);

namespace Avax\Components\HTTP\Router\System\Configuration;

use Avax\Components\Application\Container\System\Capabilities\ResolveCallable\ResolveCallable;
use Avax\Components\Application\Container\System\Capabilities\ServiceProvider\ServiceProvider;
use Avax\Components\Application\Container\System\PublicSurface\ContainerInterface;
use Avax\Components\HTTP\Dispatcher\System\Capabilities\ActionResolution\ControllerResolver;
use Avax\Components\HTTP\Router\System\Capabilities\ErrorResponseBuilding\BuildErrorResponse;
use Avax\Components\HTTP\Router\System\Capabilities\MiddlewarePipeline\BuildPipeline;
use Avax\Components\HTTP\Router\System\Capabilities\ResponseNormalization\NormalizeControllerResult;
use Avax\Components\HTTP\Router\System\Capabilities\RouteCollection\RouteCollection;
use Avax\Components\HTTP\Router\System\Capabilities\RouteExecution\InvokeRouteAction;
use Avax\Components\HTTP\Router\System\Capabilities\UrlBuilding\SubstituteRouteParameters;
use Avax\Components\HTTP\Router\System\Flows\MatchRoute\MatchRoute;
use Avax\Components\HTTP\Router\System\PublicSurface\Router;
use Avax\Components\HTTP\Router\System\PublicSurface\RouterInterface;

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

        // Router-related capabilities
        $container->singleton(BuildPipeline::class, static fn (ContainerInterface $c) : BuildPipeline => new BuildPipeline(
            callableResolver: $c->get(ResolveCallable::class),
        ),
        );
        $container->singleton(BuildErrorResponse::class, static fn () : BuildErrorResponse => new BuildErrorResponse());
        $container->singleton(SubstituteRouteParameters::class, static fn () : SubstituteRouteParameters => new SubstituteRouteParameters());
        $container->singleton(NormalizeControllerResult::class, static fn () : NormalizeControllerResult => new NormalizeControllerResult());
        $container->singleton(InvokeRouteAction::class, static fn (ContainerInterface $c) : InvokeRouteAction => new InvokeRouteAction(
            responseNormalizer: $c->get(NormalizeControllerResult::class),
        ));

        // Router — uses injected capabilities, resolves middleware through BuildPipeline
        $container->singleton(Router::class, static fn (ContainerInterface $c) : Router => new Router(
            routeCollection : $c->get(RouteCollection::class),
            matchRoute      : $c->get(MatchRoute::class),
            pipelineBuilder : $c->get(BuildPipeline::class),
            errorResponse   : $c->get(BuildErrorResponse::class),
            urlBuilder      : $c->get(SubstituteRouteParameters::class),
            invokeRouteAction: $c->get(InvokeRouteAction::class),
        ),
        );

        // RouterInterface alias — allows other components to depend on the interface
        $container->alias(RouterInterface::class, Router::class);

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
