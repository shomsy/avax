<?php

declare(strict_types=1);

/**
 * Router shortcuts for global access.
 */

use Avax\Components\HTTP\Response\ResponseFactory;
use Avax\Components\HTTP\Router\System\PublicSurface\RouterInterface;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;

if (! function_exists('route')) {
    /**
     * Generate a URL for a given route name.
     */
    function route(string $name, array $parameters = [], bool $absolute = false) : string
    {
        $router = app(RouterInterface::class);

        return $router->url($name, $parameters, $absolute);
    }
}

if (! function_exists('redirect')) {
    /**
     * Create a redirect HTTP response.
     */
    function redirect(string $url, int $status = 302) : ResponseInterface
    {
        $factory = new ResponseFactory();

        return $factory->redirect($url, $status);
    }
}

if (! function_exists('url')) {
    /**
     * Generate a fully qualified URL.
     */
    function url(string $path = '', array $parameters = []) : string
    {
        /** @var ServerRequestInterface $request */
        $request = app(ServerRequestInterface::class);
        $uri     = $request->getUri();
        $scheme  = $uri->getScheme();
        $host    = $uri->getHost();
        $port    = $uri->getPort();
        if ($port !== null) {
            $host .= ':' . $port;
        }
        $path   = ltrim($path, '/');

        if ($parameters !== []) {
            $path .= '?' . http_build_query($parameters);
        }

        return sprintf('%s://%s/%s', $scheme, $host, $path);
    }
}
