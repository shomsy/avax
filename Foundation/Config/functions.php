<?php

declare(strict_types=1);

use Avax\Config\Architecture\DDD\AppPath;
use Avax\Config\Config;

if (! function_exists(function: 'config')) {
    /**
     * Retrieve a configuration value or the entire configuration instance.
     *
     * @param string $key
     * @param mixed  $default
     *
     * @return mixed
     */
    function config(string $key, mixed $default = null) : mixed
    {
        return app(abstract: Config::class)->get(key: $key, default: $default);
    }
}

if (! function_exists(function: 'base_path')) {
    /**
     * Resolves the base path of the application.
     *
     * @param string $path The relative path to append to the base path.
     *
     * @return string The resolved base path.
     */
    function base_path(string $path = '') : string
    {
        return rtrim(string: AppPath::getRoot(), characters: '/') . '/' . ltrim(string: $path, characters: '/');
    }
}

if (! function_exists(function: 'storage_path')) {
    /**
     * Resolves the storage path.
     *
     * @param string $path
     *
     * @return string
     */
    function storage_path(string $path = '') : string
    {
        $base = base_path(path: 'storage');

        return rtrim(string: $base, characters: '/') . '/' . ltrim(string: $path, characters: '/');
    }
}
