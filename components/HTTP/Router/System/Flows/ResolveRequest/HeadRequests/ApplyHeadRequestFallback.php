<?php

declare(strict_types=1);

namespace Avax\HTTP\Router\System\Flows\ResolveRequest\HeadRequests;

use Avax\HTTP\Request\ServerRequest\IncomingRequest\ServerRequest;
use Avax\HTTP\Router\System\Flows\ResolveRequest\HttpRequestRouter;
use Avax\HTTP\Router\System\Foundation\Exceptions\InvalidConstraintException;
use Avax\HTTP\Router\System\Foundation\Exceptions\MethodNotAllowedException;
use Avax\HTTP\Router\System\Foundation\Exceptions\ReservedRouteNameException;
use Avax\HTTP\Router\System\Foundation\Exceptions\RouteNotFoundException;

/**
 * Provides fallback logic for HEAD → GET requests.
 *
 * If a HEAD route is not defined, attempts to resolve the corresponding GET route.
 */
final readonly class ApplyHeadRequestFallback
{
    private HttpRequestRouter $router;

    public function __construct(
        HttpRequestRouter $router
    )
    {
        $this->router = $router;
    }

    /**
     * Resolves the request, falling back from HEAD to GET if needed.
     *
     * @param ServerRequest $request Incoming HTTP request.
     *
     * @throws InvalidConstraintException
     */
    public function resolve(ServerRequest $request) : ServerRequest
    {
        if ($request->getMethod() !== 'HEAD') {
            return $request;
        }

        try {
            $this->router->resolve(request: $request);
        } catch (MethodNotAllowedException $exception) {
            if (in_array(needle: 'GET', haystack: $exception->allowedMethods, strict: true)) {
                $request = $request->withMethod(method: 'GET');
            }
        } catch (RouteNotFoundException) {
            $request = $request->withMethod(method: 'GET');
        } catch (ReservedRouteNameException|InvalidConstraintException $e) {
        }

        return $request;
    }
}
