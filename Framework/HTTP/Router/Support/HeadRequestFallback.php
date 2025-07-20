<?php

declare(strict_types=1);

namespace Gemini\HTTP\Router\Support;

use Gemini\HTTP\Request\Request;
use Gemini\HTTP\Router\Routing\Exceptions\RouteNotFoundException;
use Gemini\HTTP\Router\Routing\HttpRequestRouter;

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
     * @return \Gemini\HTTP\Request\Request
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
