<?php

declare(strict_types=1);

namespace Avax\Container\ServiceProviders\Providers;

use Avax\Auth\Interface\HTTP\Middleware\AuthenticationMiddleware;
use Avax\Auth\Interface\HTTP\Middleware\PermissionMiddleware;
use Avax\Auth\Interface\HTTP\Middleware\RoleMiddleware;
use Avax\Config\Architecture\DDD\AppPath;
use Avax\Container\ServiceProviders\ServiceProvider;
use Avax\HTTP\Middleware\CorsMiddleware;
use Avax\HTTP\Middleware\CSRF\CsrfMiddleware;
use Avax\HTTP\Middleware\ExceptionHandlerMiddleware;
use Avax\HTTP\Middleware\JsonResponseMiddleware;
use Avax\HTTP\Middleware\MiddlewareGroupResolver;
use Avax\HTTP\Middleware\MiddlewarePipeline;
use Avax\HTTP\Middleware\MiddlewarePipelineLogger;
use Avax\HTTP\Middleware\MiddlewareResolver;
use Avax\HTTP\Middleware\RateLimiterMiddleware;
use Avax\HTTP\Middleware\RequestLoggerMiddleware;
use Avax\HTTP\Middleware\SecurityHeadersMiddleware;
use Avax\HTTP\Middleware\SessionLifecycleMiddleware;
use Avax\HTTP\Response\ResponseFactory;
use Psr\Log\LoggerInterface;

/**
 * Registers and configures middleware components used throughout the application.
 *
 * This provider ensures centralized and DI-compliant binding of all middleware, middleware groups,
 * and the core pipeline orchestration infrastructure.
 */
final class MiddlewareServiceProvider extends ServiceProvider
{
    /**
     * Registers all middleware-related services into the container.
     */
    public function register() : void
    {
        $this->registerPipelineInfrastructure();
        $this->registerMiddlewareCore();
        $this->registerIndividualMiddlewares();
        $this->registerResolver();
        $this->registerGroupResolver();
    }

    /**
     * Registers the core pipeline and middleware manager infrastructure.
     */
    private function registerPipelineInfrastructure() : void
    {
        $this->dependencyInjector->singleton(
            abstract: MiddlewarePipeline::class,
            concrete: static fn() : MiddlewarePipeline => new MiddlewarePipeline()
        );

        $this->dependencyInjector->singleton(
            abstract: MiddlewarePipelineLogger::class,
            concrete: fn() => new MiddlewarePipelineLogger(
                logger: $this->dependencyInjector->get(LoggerInterface::class)
            )
        );
    }

    /**
     * Registers middlewares that require dependency injection manually.
     */
    private function registerMiddlewareCore() : void
    {
        $this->dependencyInjector->singleton(
            abstract: ExceptionHandlerMiddleware::class,
            concrete: fn() => new ExceptionHandlerMiddleware(
                logger:          $this->dependencyInjector->get(LoggerInterface::class),
                responseFactory: $this->dependencyInjector->get(ResponseFactory::class)
            )
        );
    }

    /**
     * Registers stateless singleton middleware instances with no required dependencies.
     */
    private function registerIndividualMiddlewares() : void
    {
        $middlewares = [
            AuthenticationMiddleware::class,
            PermissionMiddleware::class,
            RoleMiddleware::class,
            CorsMiddleware::class,
            CsrfMiddleware::class,
            JsonResponseMiddleware::class,
            RateLimiterMiddleware::class,
            RequestLoggerMiddleware::class,
            SecurityHeadersMiddleware::class,
            SessionLifecycleMiddleware::class,
        ];

        foreach ($middlewares as $middleware) {
            $this->dependencyInjector->singleton(abstract: $middleware, concrete: $middleware);
        }
    }

    /**
     * Registers a singleton instance of the middleware resolver.
     * The resolver is responsible for resolving middleware groups and handles their configuration dependencies.
     *
     * @return void
     */
    private function registerResolver() : void
    {
        $this->dependencyInjector->singleton(
            abstract: MiddlewareResolver::class,
            concrete: fn() => new MiddlewareResolver(
                groupResolver: $this->dependencyInjector->get(MiddlewareGroupResolver::class)
            )
        );
    }

    /**
     * Registers the `MiddlewareGroupResolver` class as a singleton in the dependency injector.
     *
     * This method ensures that only one instance of `MiddlewareGroupResolver` is created
     * during the application's lifecycle while managing its dependency injection.
     */
    private function registerGroupResolver() : void
    {
        // Registering MiddlewareGroupResolver as a singleton within the DependencyInjector,
        // ensuring that a single instance of the resolver is shared across the application's lifecycle.
        $this->dependencyInjector->singleton(
        // The abstract class or interface to bind to.
            abstract: MiddlewareGroupResolver::class,

            // The concrete implementation or closure defining how to resolve the abstract type.
            // In this case, an anonymous function creates and returns a new instance of MiddlewareGroupResolver.
            concrete: static fn() => new MiddlewareGroupResolver(
            // Injecting the configuration array for middleware groups, loaded from the middleware configuration file.
                config: require AppPath::CONFIG->get() . '/' . 'middleware.php'
            )
        );
    }

    /**
     * Boots global and grouped middleware for runtime execution.
     */
    public function boot() : void {}
}
