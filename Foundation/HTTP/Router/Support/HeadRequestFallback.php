<?php

declare(strict_types=1);

namespace Avax\HTTP\Router\Support;

use Avax\HTTP\Request\Request;
use Avax\HTTP\Router\Routing\Exceptions\RouteNotFoundException;
use Avax\HTTP\Router\Routing\HttpRequestRouter;

/**
 * Provides fallback logic for HEAD → GET requests.
 *
 * If a HEAD route is not defined, attempts to resolve the corresponding GET route.
 */
final class HeadRequestFallback
{
    public function __construct(
        private readonly HttpRequestRouter $router
    ) {}

    /**
     * Resolves the request, falling back from HEAD to GET if needed.
     *
     * @param Request $request Incoming HTTP request.
     *
     * @return \Avax\HTTP\Request\Request
     *
     */
    public function resolve(Request $request) : Request
    {
        if ($request->getMethod() !== 'HEAD') {
            return $request;
        }

        try {
            $this->router->resolve(request: $request);
        } catch (RouteNotFoundException) {
            // Attempt GET route fallback
            $request = $request->withMethod(method: 'GET');
        }

        return $request;
    }
}
