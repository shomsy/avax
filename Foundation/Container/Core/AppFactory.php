<?php

declare(strict_types=1);

namespace Avax\Container\Core;

use Avax\Container\Container;
use Avax\Container\Features\Core\Contracts\ServiceProviderInterface;
use Avax\Container\Http\HttpApplication;
use Avax\HTTP\Router\RouterRuntimeInterface;
use InvalidArgumentException;
use Psr\Log\LoggerInterface;
use Psr\Log\NullLogger;

/**
 * Deterministic application factory for HTTP workloads.
 *
 * @see docs/Core/AppFactory.md#quick-summary
 */
final class AppFactory
{
    /**
     * Build an HTTP application with deterministic provider order.
     *
     * @param array<int,string|ServiceProviderInterface> $providers
     *
     * @see docs/Core/AppFactory.md#method-http
     */
    public static function http(array $providers, string $routes, string $cacheDir, bool $debug = false) : HttpApplication
    {
        $container = (new ContainerBuilder)->build(cacheDir: $cacheDir, debug: $debug);

        // Provide default logger if not defined
        if (! $container->has(id: LoggerInterface::class)) {
            $container->instance(abstract: LoggerInterface::class, instance: new NullLogger);
        }

        $instances = self::registerProviders(
            container: $container,
            providers: $providers
        );

        self::bootProviders(providers: $instances);

        // Set global container for helpers and facades
        appInstance(instance: $container);

        $router = $container->get(id: RouterRuntimeInterface::class);

        // Load routes with clean DSL surface
        $router->loadRoutes(routesPath: $routes, cacheDir: '');

        return new HttpApplication(container: $container, router: $router);
    }

    /**
     * @param array<int,string|ServiceProviderInterface> $providers
     *
     * @return array<int,ServiceProviderInterface>
     */
    private static function registerProviders(Container $container, array $providers) : array
    {
        $instances = [];

        foreach ($providers as $provider) {
            $instance = is_string($provider) ? new $provider($container) : $provider;

            if (! $instance instanceof ServiceProviderInterface) {
                throw new InvalidArgumentException(message: 'Provider must implement ServiceProviderInterface.');
            }

            $instance->register();
            $instances[] = $instance;
        }

        return $instances;
    }

    /**
     * @param array<int,ServiceProviderInterface> $providers
     */
    private static function bootProviders(array $providers) : void
    {
        foreach ($providers as $provider) {
            $provider->boot();
        }
    }

    /**
     * Build a CLI-ready container without routing concerns.
     *
     * @param array<int,string|ServiceProviderInterface> $providers
     *
     * @see docs/Core/AppFactory.md#method-cli
     */
    public static function cli(array $providers, string $cacheDir, bool $debug = false) : Container
    {
        $container = (new ContainerBuilder)->build(cacheDir: $cacheDir, debug: $debug);
        $instances = self::registerProviders(container: $container, providers: $providers);
        self::bootProviders(providers: $instances);

        return $container;
    }
}