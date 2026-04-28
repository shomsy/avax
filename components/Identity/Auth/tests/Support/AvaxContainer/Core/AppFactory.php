<?php

declare(strict_types=1);

namespace Avax\Components\Application\Container\Core;

use Avax\Components\Application\Container\Providers\ServiceProvider;

final class AppFactory
{
    /**
     * @param array<int, class-string<ServiceProvider>|ServiceProvider> $providers
     */
    public static function cli(array $providers, string $cacheDir) : Container
    {
        $container = new Container();

        foreach ($providers as $provider) {
            $provider      = is_string(value: $provider) ? new $provider() : $provider;
            $provider->app = $container;
            $provider->register();
        }

        return $container;
    }
}
