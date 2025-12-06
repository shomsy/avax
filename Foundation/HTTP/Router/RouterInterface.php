<?php

declare(strict_types=1);

namespace Avax\HTTP\Router;

use Avax\HTTP\Request\Request;
use Avax\HTTP\Router\Routing\RouteRegistrarProxy;
use Psr\Http\Message\ResponseInterface;

/**
 * Interface RouterInterface
 *
 * Provides a contract for a router implementation that handles HTTP route
 * registration and resolution while enabling fallback and fluent style registration methods.
 */
interface RouterInterface
{
    /**
     * Registers a GET route.
     *
     * @param string                $path   The URL path for the route.
     * @param callable|array|string $action The action to be called when the route matches.
     *                                      Can be a callable, an array (e.g., controller and method), or a string
     *                                      (e.g., controller@method).
     *
     * @return RouteRegistrarProxy Returns a proxy for chaining extra route configurations.
     */
    public function get(string $path, callable|array|string $action) : RouteRegistrarProxy;

    /**
     * Registers a POST route.
     *
     * @param string                $path   The URL path for the route.
     * @param callable|array|string $action The action to be handled when the route matches.
     *
     * @return RouteRegistrarProxy A proxy for fluent method chaining.
     */
    public function post(string $path, callable|array|string $action) : RouteRegistrarProxy;

    /**
     * Registers a PUT route.
     *
     * @param string                $path   The URL path for the route.
     * @param callable|array|string $action The action to be executed on matching the route.
     *
     * @return RouteRegistrarProxy A proxy object for fluent route customization.
     */
    public function put(string $path, callable|array|string $action) : RouteRegistrarProxy;

    /**
     * Registers a PATCH route.
     *
     * @param string                $path   The URL path for the route.
     * @param callable|array|string $action The action to be processed when the route matches.
     *
     * @return RouteRegistrarProxy Returns a proxy for additional route configuration.
     */
    public function patch(string $path, callable|array|string $action) : RouteRegistrarProxy;

    /**
     * Registers a DELETE route.
     *
     * @param string                $path   The URL path for the route.
     * @param callable|array|string $action The action to be applied when the route matches.
     *
     * @return RouteRegistrarProxy A proxy object for chaining route details.
     */
    public function delete(string $path, callable|array|string $action) : RouteRegistrarProxy;

    /**
     * Registers an OPTIONS route.
     *
     * @param string                $path   The URL path for the route.
     * @param callable|array|string $action The action handling the route on match.
     *
     * @return RouteRegistrarProxy RouteRegistrarProxy for additional route setups.
     */
    public function options(string $path, callable|array|string $action) : RouteRegistrarProxy;

    /**
     * Registers a HEAD route.
     *
     * @param string                $path   The URL path for the route.
     * @param callable|array|string $action The action performed when the route matches.
     *
     * @return RouteRegistrarProxy An object for further configuration of the route.
     */
    public function head(string $path, callable|array|string $action) : RouteRegistrarProxy;

    /**
     * Registers the same action for all HTTP methods.
     *
     * @param string                $path   The URL path for the route.
     * @param callable|array|string $action The action to be executed for any HTTP method.
     *
     * @return RouteRegistrarProxy[] Array of proxies, each corresponding to the registered method.
     */
    public function any(string $path, callable|array|string $action) : array;

    /**
     * Sets a fallback route to be executed if no other routes match.
     *
     * @param callable|array|string $handler The fallback handler to be called when no route matches the request.
     */
    public function fallback(callable|array|string $handler) : void;

    /**
     * Resolves an incoming request into a response.
     *
     * @param Request $request The current HTTP request to be resolved.
     *
     * @return ResponseInterface The PSR-7 compliant response for the resolved request.
     */
    public function resolve(Request $request) : ResponseInterface;
}