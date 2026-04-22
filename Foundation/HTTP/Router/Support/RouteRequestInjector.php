<?php

declare(strict_types=1);

namespace Avax\HTTP\Router\Support;

use Avax\HTTP\Request\Request;
use Avax\HTTP\Router\Routing\RouteDefinition;

/**
 * Handles injection of route parameters and defaults into HTTP requests.
 *
 * Centralizes the logic for injecting route parameters and default values
 * into PSR-7 request objects, eliminating code duplication between
 * RouterKernel and HttpRequestRouter.
 */
final class RouteRequestInjector
{
    /**
     * Injects route parameters and default values into the request.
     *
     * @param Request         $request The HTTP request to modify
     * @param RouteDefinition $route   The route definition containing parameters and defaults
     *
     * @return Request The modified request with injected attributes
     */
    public static function inject(Request $request, RouteDefinition $route) : Request
    {
        // Inject route parameters as attributes into the request
        foreach ($route->parameters as $key => $value) {
            $request = $request->withAttribute(name: $key, value: $value);
        }

        // Inject default values for attributes that are not already set
        foreach ($route->defaults as $key => $value) {
            if ($request->getAttribute(name: $key) === null) {
                $request = $request->withAttribute(name: $key, value: $value);
            }
        }

        return $request;
    }

    /**
     * Injects extracted parameters and route defaults into the request.
     *
     * This is used during route resolution when parameters are extracted
     * from the URL path and need to be merged with route defaults.
     *
     * @param Request $request    The HTTP request to modify
     * @param array   $defaults   Route default values
     * @param array   $parameters Extracted URL parameters
     *
     * @return Request The modified request with injected attributes
     */
    public static function injectExtractedParameters(Request $request, array $defaults, array $parameters) : Request
    {
        // Merge extracted parameters with route defaults
        $mergedParameters = array_merge($defaults, $parameters);

        // Set route parameters as request attributes
        foreach ($mergedParameters as $key => $value) {
            $request = $request->withAttribute(name: $key, value: $value);
        }

        return $request;
    }

    /**
     * Injects route parameters from resolution context into the request.
     *
     * This method isolates route parameters in 'route.params' attribute to prevent
     * conflicts with user-defined request attributes. Individual parameters are still
     * injected for backward compatibility, but the isolated collection takes precedence.
     *
     * @param Request         $request    The HTTP request to modify
     * @param RouteDefinition $route      The resolved route definition
     * @param array           $parameters Parameters extracted during resolution
     *
     * @return Request The modified request with injected attributes
     */
    public static function injectWithContext(Request $request, RouteDefinition $route, array $parameters) : Request
    {
        // Isolate route parameters in dedicated attribute to prevent conflicts
        $request = $request->withAttribute(name: 'route.params', value: $parameters);

        // Inject resolved parameters as individual request attributes for backward compatibility
        foreach ($parameters as $key => $value) {
            // Only inject if it doesn't conflict with existing user attributes
            if ($request->getAttribute(name: $key) === null) {
                $request = $request->withAttribute(name: $key, value: $value);
            }
        }

        // Inject any remaining route defaults that weren't resolved
        foreach ($route->defaults as $key => $value) {
            if ($request->getAttribute(name: $key) === null) {
                $request = $request->withAttribute(name: $key, value: $value);
            }
        }

        return $request;
    }
}