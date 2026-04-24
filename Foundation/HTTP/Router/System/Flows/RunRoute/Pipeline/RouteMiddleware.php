<?php

declare(strict_types=1);

namespace Avax\HTTP\Router\System\Flows\RunRoute\Pipeline;

use Avax\HTTP\Request\ServerRequest\IncomingRequest\ServerRequest;
use Closure;
use Psr\Http\Message\ResponseInterface;

/**
 * Interface for route middleware components.
 *
 * Middleware implementing this interface can be applied to routes
 * and will be executed as part of the request processing pipeline.
 */
interface RouteMiddleware
{
    /**
     * Processes the request and optionally calls the next middleware/stage.
     *
     * @param Closure(ServerRequest): ResponseInterface $next The next middleware/stage in the pipeline
     */
    public function handle(ServerRequest $request, Closure $next) : ResponseInterface;
}