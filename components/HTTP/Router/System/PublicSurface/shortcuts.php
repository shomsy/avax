<?php

declare(strict_types=1);

/**
 * Router shortcuts for global access.
 */

use Avax\Components\HTTP\Response\ResponseFactory;
use Avax\Components\HTTP\Router\System\PublicSurface\RouterInterface;
use Psr\Http\Message\ResponseInterface;

if (! function_exists('route')) {
    /**
     * Generate a URL for a given route name.
     */
    function route(string $name, array $parameters = [], bool $absolute = false): string
    {
        $router = app(RouterInterface::class);

        return $router->url($name, $parameters, $absolute);
    }
}

if (! function_exists('redirect')) {
    /**
     * Create a redirect HTTP response.
     */
    function redirect(string $url, int $status = 302): ResponseInterface
    {
        $factory = new ResponseFactory;

        return $factory->redirect($url, $status);
    }
}

if (! function_exists('url')) {
    /**
     * Generate a fully qualified URL.
     */
    function url(string $path = '', array $parameters = []): string
    {
        $scheme = (! empty($_SERVER['HTTPS']) && $_SERVER['HTTPS'] !== 'off') ? 'https' : 'http';
        $host = $_SERVER['HTTP_HOST'] ?? 'localhost';
        $path = ltrim($path, '/');

        if (! empty($parameters)) {
            $path .= '?'.http_build_query($parameters);
        }

        return "{$scheme}://{$host}/{$path}";
    }
}
