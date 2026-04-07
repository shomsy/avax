<?php

declare(strict_types=1);

namespace Avax\Container\DependencyInjection\Configuration;

use Avax\Container\Container;
use Avax\Container\DependencyInjection\Capability\Providers\Contracts\ServiceProviderInterface;
use Avax\Container\DependencyInjection\Capability\Providers\Runtime\Http\HttpApplication;
use Avax\Container\DependencyInjection\Flow\BootProviders\BootProviders;
use Avax\HTTP\Router\RouterRuntimeInterface;
use Psr\Log\LoggerInterface;
use Psr\Log\NullLogger;

/**
 * Deterministic application assembly for HTTP and CLI runtimes.
 */
final class AppFactory
{
    /** @param array<int, string|ServiceProviderInterface> $providers */
    public static function http(array $providers, string $routes, string $cacheDir, bool $debug = false) : HttpApplication
    {
        $container = (new ContainerBuilder)->build(
            config: ContainerConfig::create(cacheDir: $cacheDir, debug: $debug)
        );

        if (! $container->has(id: LoggerInterface::class)) {
            $container->instance(abstract: LoggerInterface::class, instance: new NullLogger);
        }

        (new BootProviders(container: $container))->execute(providers: $providers);
        appInstance(instance: $container);

        $router = $container->get(id: RouterRuntimeInterface::class);
        $router->loadRoutes(routesPath: $routes, cacheDir: '');

        return new HttpApplication(container: $container, router: $router);
    }

    /** @param array<int, string|ServiceProviderInterface> $providers */
    public static function cli(array $providers, string $cacheDir, bool $debug = false) : Container
    {
        $container = (new ContainerBuilder)->build(
            config: ContainerConfig::create(cacheDir: $cacheDir, debug: $debug)
        );
        (new BootProviders(container: $container))->execute(providers: $providers);

        return $container;
    }
}
