<?php

declare(strict_types=1);

namespace Gemini\Container\ServiceProviders\Providers;

use Gemini\Auth\Interface\HTTP\Middleware\AuthenticationMiddleware;
use Gemini\Auth\Interface\HTTP\Middleware\PermissionMiddleware;
use Gemini\Auth\Interface\HTTP\Middleware\RoleMiddleware;
use Gemini\Config\Architecture\DDD\AppPath;
use Gemini\Container\ServiceProviders\ServiceProvider;
use Gemini\HTTP\Middleware\CorsMiddleware;
use Gemini\HTTP\Middleware\CSRF\CsrfMiddleware;
use Gemini\HTTP\Middleware\ExceptionHandlerMiddleware;
use Gemini\HTTP\Middleware\JsonResponseMiddleware;
use Gemini\HTTP\Middleware\MiddlewareGroupResolver;
use Gemini\HTTP\Middleware\MiddlewarePipeline;
use Gemini\HTTP\Middleware\MiddlewarePipelineLogger;
use Gemini\HTTP\Middleware\MiddlewareResolver;
use Gemini\HTTP\Middleware\RateLimiterMiddleware;
use Gemini\HTTP\Middleware\RequestLoggerMiddleware;
use Gemini\HTTP\Middleware\SecurityHeadersMiddleware;
use Gemini\HTTP\Middleware\SessionLifecycleMiddleware;
use Gemini\HTTP\Response\ResponseFactory;
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
