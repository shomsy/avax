<?php

declare(strict_types=1);

namespace {
    if (! function_exists(function: 'appInstance')) {
        /**
         * Store or retrieve the global kernel container instance used by helpers.
         *
         * @param mixed|null $instance
         *
         * @return mixed
         */
        function appInstance(mixed $instance = null) : mixed
        {
            static $container = null;

            if ($instance !== null) {
                $container = $instance;
            }

            if ($container === null) {
                throw new RuntimeException(
                    message: "Container instance is not initialized. Please set the container first."
                );
            }

            return $container;
        }
    }
}
