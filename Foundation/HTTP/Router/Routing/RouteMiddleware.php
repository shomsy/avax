<?php

declare(strict_types=1);

namespace Avax\HTTP\Router\Routing;

use Avax\HTTP\Request\Request;
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
     * @param Closure(Request): ResponseInterface $next The next middleware/stage in the pipeline
     */
    public function handle(Request $request, Closure $next) : ResponseInterface;
}