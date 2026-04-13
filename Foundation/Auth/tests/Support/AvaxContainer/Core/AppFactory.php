<?php

declare(strict_types=1);

namespace Avax\Container\Core;

use Avax\Container\Providers\ServiceProvider;

final class AppFactory
{
    /**
     * @param array<int, class-string<ServiceProvider>|ServiceProvider> $providers
     */
    public static function cli(array $providers, string $cacheDir) : Container
    {
        $container = new Container();

        foreach ($providers as $provider) {
            $provider = is_string($provider) ? new $provider() : $provider;
            $provider->setApp(app: $container);
            $provider->register();
        }

        return $container;
    }
}
