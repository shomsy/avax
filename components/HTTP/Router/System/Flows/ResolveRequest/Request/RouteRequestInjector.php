<?php

declare(strict_types=1);

namespace Avax\Components\HTTP\Router\System\Flows\ResolveRequest\Request;

use Avax\Components\HTTP\Request\ServerRequest\IncomingRequest\ServerRequest;
use Avax\Components\HTTP\Router\System\Capabilities\RouteDefinition\RouteDefinition;

/**
 * Handles injection of route parameters and defaults into HTTP requests.
 */
final class RouteRequestInjector
{
    public static function inject(ServerRequest $request, RouteDefinition $route) : ServerRequest
    {
        foreach ($route->parameters as $key => $value) {
            $request = $request->withAttribute(name: $key, value: $value);
        }

        foreach ($route->defaults as $key => $value) {
            if ($request->getAttribute(name: $key) === null) {
                $request = $request->withAttribute(name: $key, value: $value);
            }
        }

        return $request;
    }

    public static function injectExtractedParameters(ServerRequest $request, array $defaults, array $parameters) : ServerRequest
    {
        $mergedParameters = array_merge($defaults, $parameters);
        foreach ($mergedParameters as $key => $value) {
            $request = $request->withAttribute(name: $key, value: $value);
        }

        return $request;
    }

    public static function injectWithContext(ServerRequest $request, RouteDefinition $route, array $parameters) : ServerRequest
    {
        $request = $request->withAttribute(name: 'route.params', value: $parameters);
        foreach ($parameters as $key => $value) {
            if ($request->getAttribute(name: $key) === null) {
                $request = $request->withAttribute(name: $key, value: $value);
            }
        }
        foreach ($route->defaults as $key => $value) {
            if ($request->getAttribute(name: $key) === null) {
                $request = $request->withAttribute(name: $key, value: $value);
            }
        }

        return $request;
    }
}
