<?php

declare(strict_types=1);

if (! function_exists(function: 'appInstance')) {
    /**
     * Store or retrieve the global kernel container instance used by helpers.
     */
    function appInstance(mixed $instance = null) : mixed
    {
        static $container = null;

        if ($instance !== null) {
            $container = $instance;
        }

        if ($container === null) {
            throw new RuntimeException(
                message: 'Container instance is not initialized. Please set the container first.'
            );
        }

        return $container;
    }
}

if (! function_exists(function: 'app')) {
    /**
     * Get the available container instance or resolve an abstract from the container.
     *
     * @param string|null $abstract
     *
     * @return mixed
     */
    function app(string|null $abstract = null) : mixed
    {
        $dependencyInjector = appInstance();

        if ($abstract === null) {
            return $dependencyInjector;
        }

        return $dependencyInjector->get(id: $abstract);
    }
}
