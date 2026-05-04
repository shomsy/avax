<?php

declare(strict_types=1);

namespace Avax\Components\HTTP\System\Flows\Routing;

use Avax\Components\HTTP\Middleware\RequestHandlerInterface;
use Avax\Components\HTTP\Request\ServerRequest\IncomingRequest\ServerRequest;
use Avax\Components\HTTP\Router\System\PublicSurface\RouterRuntimeInterface;
use Psr\Http\Message\RequestInterface;
use Psr\Http\Message\ResponseInterface;
use RuntimeException;

/**
 * Flow: Resolve Route From HTTP Request
 *
 * Resolves the matched route from an incoming HTTP request and dispatches it.
 * Migrated from the root of HTTP component.
 */
readonly class ResolveRouteFromHttpRequest implements RequestHandlerInterface
{
    public function __construct(private RouterRuntimeInterface $routerRuntime) {}

    public function handle(RequestInterface $request) : ResponseInterface
    {
        if (! $request instanceof ServerRequest) {
            throw new RuntimeException('HttpKernel requires an internal Avax HTTP ServerRequest instance for router execution.');
        }

        return $this->routerRuntime->resolve($request);
    }
}
