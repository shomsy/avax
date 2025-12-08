<?php

declare(strict_types=1);

namespace Avax\Facade\Facades;

use Avax\Facade\BaseFacade;
use Avax\HTTP\Request\Request;
use Avax\HTTP\Router\RouterInterface;
use Avax\HTTP\Router\Routing\RouteRegistrarProxy;
use Psr\Http\Message\ResponseInterface;

/**
 * Facade for interacting with the HTTP routing system.
 *
 * @method static RouteRegistrarProxy get(string $path, callable|array|string $action)
 * @method static RouteRegistrarProxy post(string $path, callable|array|string $action)
 * @method static RouteRegistrarProxy put(string $path, callable|array|string $action)
 * @method static RouteRegistrarProxy patch(string $path, callable|array|string $action)
 * @method static RouteRegistrarProxy delete(string $path, callable|array|string $action)
 * @method static RouteRegistrarProxy options(string $path, callable|array|string $action)
 * @method static RouteRegistrarProxy head(string $path, callable|array|string $action)
 * @method static RouteRegistrarProxy[] any(string $path, callable|array|string $action)
 * @method static void fallback(callable|array|string $handler)
 * @method static ResponseInterface resolve(Request $request)
 */
final class Route extends BaseFacade
{
    protected static string $accessor = RouterInterface::class;
}
