<?php

declare(strict_types=1);

/**
 * HTTP Context helper functions.
 *
 * Provide typed access to PHP superglobals through the HttpContext capability.
 */

use Avax\Components\HTTP\Context\System\PublicSurface\HttpContext;

if (! function_exists('context')) {
    /**
     * Get the HTTP context instance.
     */
    function context() : HttpContext
    {
        return HttpContext::fromGlobals();
    }
}

if (! function_exists('server')) {
    /**
     * Get a value from $_SERVER.
     */
    function server(string $key, mixed $default = null) : mixed
    {
        return $_SERVER[$key] ?? $default;
    }
}

if (! function_exists('env')) {
    /**
     * Get an environment variable value.
     */
    function env(string $key, mixed $default = null) : mixed
    {
        $value = $_ENV[$key] ?? $_SERVER[$key] ?? getenv($key);

        if ($value === false) {
            return $default;
        }

        return match (strtolower($value)) {
            'true', '(true)'   => true,
            'false', '(false)' => false,
            'null', '(null)'   => null,
            'empty', '(empty)' => '',
            default            => $value,
        };
    }
}
