<?php

declare(strict_types=1);

/**
 * Container shortcuts for global access.
 */

use Avax\Components\Application\Container\System\ContainerInterface;
use Avax\Components\Application\Container\System\PublicSurface\Container;

if (! function_exists('appInstance')) {
    /**
     * Get or set the global container instance.
     */
    function appInstance(?ContainerInterface $instance = null) : ?ContainerInterface
    {
        static $container = null;

        if ($instance instanceof ContainerInterface) {
            $container = $instance;
            Container::setContainer($instance);
        }

        return $container;
    }
}

if (! function_exists('app')) {
    /**
     * Get the container or resolve a service.
     */
    function app(?string $abstract = null) : mixed
    {
        $container = appInstance();

        if ($abstract === null) {
            return $container;
        }

        return $container->get($abstract);
    }
}

if (! function_exists('make')) {
    /**
     * Build a service from the container.
     */
    function make(string $abstract, array $parameters = []) : object
    {
        return appInstance()->make($abstract, $parameters);
    }
}

if (! function_exists('bind')) {
    /**
     * Bind a service to the container.
     */
    function bind(string $abstract, mixed $concrete = null, bool $shared = false) : void
    {
        appInstance()->bind($abstract, $concrete, $shared);
    }
}

if (! function_exists('singleton')) {
    /**
     * Register a singleton in the container.
     */
    function singleton(string $abstract, mixed $concrete = null) : void
    {
        appInstance()->singleton($abstract, $concrete);
    }
}

if (! function_exists('resolve')) {
    /**
     * Resolve a service from the container.
     */
    function resolve(string $abstract, array $parameters = []) : object
    {
        return appInstance()->make($abstract, $parameters);
    }
}
