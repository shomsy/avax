<?php

declare(strict_types=1);

namespace Avax\Components\HTTP\Router\System\PublicSurface;

use Avax\Components\HTTP\Request\System\PublicSurface\RequestInterface;
use Avax\Components\HTTP\Response\System\PublicSurface\ResponseInterface;
use Avax\Components\HTTP\Router\System\Flows\RegisterRoutes\Files\Registrar;

interface RouterInterface
{
    public function get(string $path, mixed $action) : Registrar;

    public function post(string $path, mixed $action) : Registrar;

    public function put(string $path, mixed $action) : Registrar;

    public function patch(string $path, mixed $action) : Registrar;

    public function delete(string $path, mixed $action) : Registrar;

    public function options(string $path, mixed $action) : Registrar;

    public function head(string $path, mixed $action) : Registrar;

    /**
     * Register a route for all standard HTTP methods.
     */
    public function any(string $path, mixed $action) : Registrar;

    /**
     * Register a group of routes with a common prefix and/or middleware.
     *
     * @param string                                     $prefix     Route prefix (e.g., '/api/v1')
     * @param callable                                   $groupFn    Callback receiving RouterInterface to register
     *                                                               routes
     * @param string|callable|list<string|callable>|null $middleware Optional group middleware
     */
    public function group(string $prefix, callable $groupFn, string|array|callable|null $middleware = null) : void;

    /**
     * Register a fallback route for unmatched requests.
     */
    public function fallback(mixed $handler) : void;

    /**
     * Generate a URL for a named route.
     *
     * @param string               $name       Route name
     * @param array<string, mixed> $parameters Route parameters
     * @param bool                 $absolute   Whether to generate an absolute URL
     */
    public function url(string $name, array $parameters = [], bool $absolute = false) : string;

    public function dispatch(RequestInterface $request) : ResponseInterface;
}
