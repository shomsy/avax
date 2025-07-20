<?php

declare(strict_types=1);

namespace Gemini\Container\ServiceProviders\Providers;

use Gemini\Container\ServiceProviders\ServiceProvider;
use Gemini\HTTP\Dispatcher\ControllerDispatcher;
use Gemini\HTTP\Middleware\MiddlewareResolver;
use Gemini\HTTP\Router\Bootstrap\RouteBootstrapper;
use Gemini\HTTP\Router\Cache\RouteCacheCompiler;
use Gemini\HTTP\Router\Cache\RouteCacheLoader;
use Gemini\HTTP\Router\Kernel\RouterKernel;
use Gemini\HTTP\Router\Router;
use Gemini\HTTP\Router\RouterInterface;
use Gemini\HTTP\Router\Routing\HttpRequestRouter;
use Gemini\HTTP\Router\Routing\RouteGroupRegistrar;
use Gemini\HTTP\Router\Routing\RoutePipelineFactory;
use Gemini\HTTP\Router\Support\HeadRequestFallback;
use Gemini\HTTP\Router\Validation\RouteConstraintValidator;
use Psr\Log\LoggerInterface;

/**
 * Service Provider for setting up the routing services.
 * This class is responsible for registering and booting the necessary services related to routing.
 */
class RouterServiceProvider extends ServiceProvider
{
    /**
     * Register routing-related services in the container.
     * Using singleton to ensure a single instance of these classes is used throughout the application.
     */
    public function register() : void
    {
        $this->dependencyInjector->singleton(
            abstract: RouterInterface::class,
            concrete: fn() => $this->dependencyInjector->get(Router::class)
        );

        $this->dependencyInjector->singleton(
            abstract: RouteConstraintValidator::class,
            concrete: static fn() => new RouteConstraintValidator()
        );

        $this->dependencyInjector->singleton(
            abstract: HttpRequestRouter::class,
            concrete: fn() : HttpRequestRouter => new HttpRequestRouter(
                constraintValidator: $this->dependencyInjector->get(RouteConstraintValidator::class)
            )
        );

        $this->dependencyInjector->singleton(
            abstract: ControllerDispatcher::class,
            concrete: fn() : ControllerDispatcher => new ControllerDispatcher(
                container: $this->dependencyInjector
            )
        );

        $this->dependencyInjector->singleton(
            abstract: HeadRequestFallback::class,
            concrete: fn() => new HeadRequestFallback(
                router: $this->dependencyInjector->get(id: HttpRequestRouter::class)
            )
        );

        $this->dependencyInjector->singleton(
            abstract: RoutePipelineFactory::class,
            concrete: fn() => new RoutePipelineFactory(
                container         : $this->dependencyInjector,
                dispatcher        : $this->dependencyInjector->get(ControllerDispatcher::class),
                middlewareResolver: $this->dependencyInjector->get(MiddlewareResolver::class)
            )
        );

        $this->dependencyInjector->singleton(
            abstract: RouterKernel::class,
            concrete: fn() : RouterKernel => new RouterKernel(
                httpRequestRouter  : $this->dependencyInjector->get(id: HttpRequestRouter::class),
                pipelineFactory    : $this->dependencyInjector->get(id: RoutePipelineFactory::class),
                headRequestFallback: $this->dependencyInjector->get(id: HeadRequestFallback::class)
            )
        );

        $this->dependencyInjector->singleton(
            abstract: RouteConstraintValidator::class,
            concrete: static fn() => new RouteConstraintValidator()
        );

        $this->dependencyInjector->singleton(
            abstract: Router::class,
            concrete: fn() : Router => new Router(
                httpRequestRouter: $this->dependencyInjector->get(id: HttpRequestRouter::class),
                kernel           : $this->dependencyInjector->get(id: RouterKernel::class),
            )
        );

        $this->dependencyInjector->singleton(
            abstract: RouteGroupRegistrar::class,
            concrete: static fn() : RouteGroupRegistrar => new RouteGroupRegistrar()
        );

        // Optional alias
        $this->dependencyInjector->singleton(
            abstract: 'Route',
            concrete: fn() => $this->dependencyInjector->get(id: Router::class)
        );

        $this->dependencyInjector->singleton(
            abstract: RouteCacheCompiler::class,
            concrete: static fn() => new RouteCacheCompiler()
        );

        $this->dependencyInjector->singleton(
            abstract: RouteCacheLoader::class,
            concrete: fn() => new RouteCacheLoader(
                router: $this->dependencyInjector->get(Router::class)
            )
        );

        $this->dependencyInjector->singleton(
            abstract: RouteBootstrapper::class,
            concrete: fn() => new RouteBootstrapper(
                routeCacheLoader : $this->dependencyInjector->get(RouteCacheLoader::class),
                httpRequestRouter: $this->dependencyInjector->get(HttpRequestRouter::class),
                logger           : $this->dependencyInjector->get(LoggerInterface::class),
            )
        );
    }

    /**
     * Boot the router service provider, ensuring configuration settings are loaded.
     *
     * @throws \Throwable
     */
    public function boot() : void
    {
        /** @var RouteBootstrapper $bootstrapper */
        $bootstrapper = $this->dependencyInjector->get(RouteBootstrapper::class);
        $bootstrapper->bootstrap();
    }
}