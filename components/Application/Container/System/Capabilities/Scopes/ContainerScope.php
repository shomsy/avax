<?php

declare(strict_types=1);

namespace Avax\Components\Application\Container\System\Capabilities\Scopes;

use Avax\Components\Application\Container\System\PublicSurface\Container;

final class ContainerScope
{
    public static function make(string $abstract): object
    {
        return Container::make($abstract);
    }

    public static function bind(string $abstract, callable $factory): void
    {
        Container::bind($abstract, $factory);
    }

    public static function singleton(string $abstract, callable $factory): void
    {
        Container::singleton($abstract, $factory);
    }

    public static function instance(string $abstract, mixed $instance): void
    {
        Container::instance($abstract, $instance);
    }
}
