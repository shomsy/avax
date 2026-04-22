<?php

declare(strict_types=1);

namespace Avax\Facade\Facades;

use Avax\Facade\BaseFacade;
use Avax\HTTP\Request\Request;
use Avax\HTTP\Router\RouterInterface;
use Avax\HTTP\Router\Routing\HttpRequestRouter;
use Avax\HTTP\Router\Routing\RouteDefinition;
use Avax\HTTP\Router\Routing\RouteRegistrarProxy;
use Closure;
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
 * @method static void group(array $attributes, Closure $callback)
 * @method static self prefix(string $prefix)
 * @method static self middleware(array $middleware)
 * @method static self where(string $param, string $pattern)
 * @method static self whereIn(array $constraints)
 * @method static self defaults(array $defaults)
 * @method static self attributes(array $attributes)
 * @method static self name(string $prefix)
 * @method static self domain(string $domain)
 * @method static self authorize(string $policy)
 * @method static void registerRouteFromCache(RouteDefinition $definition)
 * @method static RouteDefinition getRouteByName(string $name)
 * @method static array allRoutes()
 * @method static HttpRequestRouter getHttpRouter()
 */
final class Route extends BaseFacade
{
    protected static string $accessor = RouterInterface::class;
}
