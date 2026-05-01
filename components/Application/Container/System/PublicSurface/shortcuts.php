<?php

declare(strict_types=1);

/**
 * Container shortcuts for global access.
 */

use Avax\Components\Application\Container\System\Foundation\DIContainerInterface;
use Avax\Components\Application\Container\System\PublicSurface\Container;
use Avax\Components\Application\Container\System\PublicSurface\ContainerFacade;

if (! function_exists('appInstance')) {
    /**
     * Get or set the global container instance.
     *
     * @param DIContainerInterface|null $instance
     *
     * @return DIContainerInterface|null
     */
    function appInstance(DIContainerInterface|null $instance = null) : DIContainerInterface|null
    {
        static $container = null;

        if ($instance instanceof DIContainerInterface) {
            $container = $instance;
            ContainerFacade::setContainer($instance);
            Container::setContainer($instance);
        }

        return $container;
    }
}

if (! function_exists('app')) {
    /**
     * Get the container or resolve a service.
     *
     *
     */
    function app(string|null $abstract = null) : mixed
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
     *
     *
     */
    function make(string $abstract, array $parameters = []) : object
    {
        return appInstance()->make($abstract, $parameters);
    }
}

if (! function_exists('bind')) {
    /**
     * Bind a service to the container.
     *
     * @param mixed|null $concrete
     */
    function bind(string $abstract, mixed $concrete = null, bool $shared = false) : void
    {
        appInstance()->bind($abstract, $concrete, $shared);
    }
}

if (! function_exists('singleton')) {
    /**
     * Register a singleton in the container.
     *
     * @param mixed|null $concrete
     */
    function singleton(string $abstract, mixed $concrete = null) : void
    {
        appInstance()->singleton($abstract, $concrete);
    }
}

if (! function_exists('resolve')) {
    /**
     * Resolve a service from the container.
     *
     *
     */
    function resolve(string $abstract, array $parameters = []) : object
    {
        return appInstance()->make($abstract, $parameters);
    }
}
