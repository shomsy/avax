<?php

declare(strict_types=1);

/**
 * Configuration shortcuts for global access.
 */

use Avax\Components\Application\Config\Architecture\DDD\AppPath;
use Avax\Components\Application\Config\Service\Config;

if (! function_exists('config')) {
    /**
     * Retrieve a configuration value or the entire configuration instance.
     */
    function config(string $key, mixed $default = null) : mixed
    {
        return app(Config::class)->get($key, $default);
    }
}

if (! function_exists('base_path')) {
    /**
     * Resolves the base path of the application.
     *
     * @param string $path The relative path to append to the base path.
     * @return string The resolved base path.
     */
    function base_path(string $path = '') : string
    {
        return rtrim(AppPath::getRoot(), '/') . '/' . ltrim($path, '/');
    }
}

if (! function_exists('storage_path')) {
    /**
     * Resolves the storage path.
     */
    function storage_path(string $path = '') : string
    {
        $base = base_path('storage');

        return rtrim($base, '/') . '/' . ltrim($path, '/');
    }
}
