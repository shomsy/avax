<?php

declare(strict_types=1);

namespace Avax\Container\Providers\HTTP;

use Avax\Container\Features\Core\Contracts\ContainerInterface;
use Avax\Container\Providers\ServiceProvider;
use Avax\HTTP\Dispatcher\ControllerDispatcher;
use Avax\HTTP\Router\Bootstrap\RouteBootstrapState;
use Avax\HTTP\Router\Cache\RouteCacheLoader;
use Avax\HTTP\Router\Kernel\RouterKernel;
use Avax\HTTP\Router\Matching\RouteMatcherRegistry;
use Avax\HTTP\Router\Router;
use Avax\HTTP\Router\RouterDsl;
use Avax\HTTP\Router\RouterInterface;
use Avax\HTTP\Router\RouterRuntimeInterface;
use Avax\HTTP\Router\Routing\DomainAwareMatcher;
use Avax\HTTP\Router\Routing\ErrorResponseFactory;
use Avax\HTTP\Router\Routing\HttpRequestRouter;
use Avax\HTTP\Router\Routing\RouteExecutor;
use Avax\HTTP\Router\Routing\RouteGroupStack;
use Avax\HTTP\Router\Routing\RouteMatcher;
use Avax\HTTP\Router\Routing\RoutePipelineFactory;
use Avax\HTTP\Router\Routing\RouterRegistrar;
use Avax\HTTP\Router\Routing\StageChain;
use Avax\HTTP\Router\Support\FallbackManager;
use Avax\HTTP\Router\Support\HeadRequestFallback;
use Avax\HTTP\Router\Support\RouterBootstrapState;
use Avax\HTTP\Router\Support\RouteRegistry;
use Avax\HTTP\Router\Tracing\RouterTrace;
use Avax\HTTP\Router\Validation\RouteConstraintValidator;
use Psr\Log\LoggerInterface;

/**
 * Service Provider for routing services.
 *
 * @see docs/Providers/HTTP/RouterServiceProvider.md#quick-summary
 */
class RouterServiceProvider extends ServiceProvider
{
    /**
     * Register router bindings, validator, kernel, and alias.
     *
     * @see docs/Providers/HTTP/RouterServiceProvider.md#method-register
     */
    public function register() : void
    {
        $this->app->singleton(abstract: RouteConstraintValidator::class, concrete: RouteConstraintValidator::class);

        $this->app->singleton(abstract: RouteMatcher::class, concrete: function () {
            return new RouteMatcher(logger: $this->app->get(id: LoggerInterface::class));
        });

        $this->app->singleton(abstract: DomainAwareMatcher::class, concrete: function () {
            return new DomainAwareMatcher(
                baseMatcher: $this->app->get(id: RouteMatcher::class)
            );
        });

        $this->app->singleton(abstract: RouterTrace::class, concrete: RouterTrace::class);

        $this->app->singleton(abstract: RouteMatcherRegistry::class, concrete: function () {
            return RouteMatcherRegistry::withDefaults(logger: $this->app->get(id: LoggerInterface::class));
        });

        $this->app->singleton(abstract: HttpRequestRouter::class, concrete: function () {
            $registry = $this->app->get(id: RouteMatcherRegistry::class);

            return new HttpRequestRouter(
                constraintValidator: $this->app->get(id: RouteConstraintValidator::class),
                matcher            : $registry->get(key: 'domain'), // Default to domain-aware matcher
                logger             : $this->app->get(id: LoggerInterface::class),
                trace              : $this->app->get(id: RouterTrace::class)
            );
        });

        // Core dispatcher/pipeline components (required in strict mode)
        $this->app->singleton(abstract: ControllerDispatcher::class, concrete: ControllerDispatcher::class);
        // Core routing pipeline factory (ensures strict-mode availability)
        $this->app->singleton(abstract: RoutePipelineFactory::class, concrete: RoutePipelineFactory::class);

        $this->app->singleton(abstract: Router::class, concrete: function () {
            return new Router(
                httpRequestRouter: $this->app->get(id: HttpRequestRouter::class),
                kernel           : $this->app->get(id: RouterKernel::class),
                fallbackManager  : $this->app->get(id: FallbackManager::class),
                errorFactory     : $this->app->get(id: ErrorResponseFactory::class),
                dslRouter        : $this->app->get(id: RouterInterface::class),
                groupStack       : $this->app->get(id: RouteGroupStack::class),
                routeRegistry    : $this->app->get(id: RouteRegistry::class)
            );
        });
        $this->app->singleton(abstract: RouterRuntimeInterface::class, concrete: Router::class);
        $this->app->singleton(abstract: RouterKernel::class, concrete: function () {
            return new RouterKernel(
                httpRequestRouter  : $this->app->get(id: HttpRequestRouter::class),
                pipelineFactory    : $this->app->get(id: RoutePipelineFactory::class),
                headRequestFallback: $this->app->get(id: HeadRequestFallback::class),
                routeExecutor      : $this->app->get(id: RouteExecutor::class)
            );
        });
        $this->app->singleton(abstract: HeadRequestFallback::class, concrete: HeadRequestFallback::class);

        $this->app->singleton(abstract: RouteRegistry::class, concrete: RouteRegistry::class);

        $this->app->singleton(abstract: ErrorResponseFactory::class, concrete: ErrorResponseFactory::class);

        $this->app->singleton(abstract: RouteExecutor::class, concrete: function () {
            return new RouteExecutor(controllerDispatcher: $this->app->get(id: ControllerDispatcher::class));
        });

        $this->app->singleton(abstract: RouterRegistrar::class, concrete: function () {
            return new RouterRegistrar(
                registry         : $this->app->get(id: RouteRegistry::class),
                httpRequestRouter: $this->app->get(id: HttpRequestRouter::class)
            );
        });

        $this->app->singleton(abstract: FallbackManager::class, concrete: function () {
            return new FallbackManager(dispatcher: $this->app->get(id: ControllerDispatcher::class));
        });

        $this->app->singleton(abstract: RouteGroupStack::class, concrete: RouteGroupStack::class);

        $this->app->singleton(abstract: RouterInterface::class, concrete: function () {
            return new RouterDsl(
                registrar           : $this->app->get(id: RouterRegistrar::class),
                router              : $this->app->get(id: HttpRequestRouter::class),
                controllerDispatcher: $this->app->get(id: ControllerDispatcher::class),
                fallbackManager     : $this->app->get(id: FallbackManager::class),
                groupStack          : $this->app->get(id: RouteGroupStack::class),
                registry            : $this->app->get(id: RouteRegistry::class),
            );
        });

        $this->app->singleton(abstract: StageChain::class, concrete: function () {
            return new StageChain(
                container: $this->app->get(id: ContainerInterface::class),
                logger   : $this->app->get(id: LoggerInterface::class)
            );
        });

        // Bootstrap-related services
        $this->app->singleton(abstract: RouteCacheLoader::class, concrete: RouteCacheLoader::class);
        $this->app->singleton(abstract: \Avax\HTTP\Router\Support\RouteCacheLoader::class, concrete: RouteCacheLoader::class);
        $this->app->singleton(abstract: RouterBootstrapState::class, concrete: RouterBootstrapState::class);
        $this->app->singleton(abstract: RouteBootstrapState::class, concrete: RouterBootstrapState::class);

        // Bind 'router' alias
        $this->app->singleton(abstract: 'router', concrete: function () {
            return $this->app->get(id: Router::class);
        });
    }
}