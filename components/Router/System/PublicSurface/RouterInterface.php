<?php

declare(strict_types=1);

namespace Avax\Components\Router\System\PublicSurface;

use Avax\Components\Router\System\Flows\RegisterRoutes\Files\RouteRegistrarProxy;

/**
 * Public API Contract: Router DSL Interface.
 */
interface RouterInterface
{
    public function get(string $path, callable|array|string $action) : RouteRegistrarProxy;

    public function post(string $path, callable|array|string $action) : RouteRegistrarProxy;

    public function put(string $path, callable|array|string $action) : RouteRegistrarProxy;

    public function patch(string $path, callable|array|string $action) : RouteRegistrarProxy;

    public function delete(string $path, callable|array|string $action) : RouteRegistrarProxy;

    public function options(string $path, callable|array|string $action) : RouteRegistrarProxy;

    public function head(string $path, callable|array|string $action) : RouteRegistrarProxy;

    public function any(string $path, callable|array|string $action) : RouteRegistrarProxy;

    public function fallback(callable|array|string $handler) : void;
}
