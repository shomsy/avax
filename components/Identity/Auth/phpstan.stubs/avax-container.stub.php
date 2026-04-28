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
        return new Container();
    }
}

final class Container
{
    public function has(string $id) : bool
    {
        return false;
    }

    public function get(string $id) : mixed
    {
        return null;
    }

    public function singleton(string $id, mixed $implementation = null) : void {}

    public function instance(string $id, mixed $implementation) : void {}
}

namespace Avax\Components\Application\Container\Providers;

use Avax\Components\Application\Container\Core\Container;

abstract class ServiceProvider
{
    protected Container $app;

    abstract public function register() : void;
}
