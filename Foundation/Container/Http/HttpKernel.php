<?php

declare(strict_types=1);

namespace Avax\Container\Http;

use Avax\HTTP\Middleware\MiddlewarePipeline;
use Avax\HTTP\Request\Request;
use Avax\HTTP\Router\RouterRuntimeInterface;
use Psr\Http\Message\ResponseInterface;

/**
 * Middleware-aware HTTP kernel that delegates to the router.
 */
final readonly class HttpKernel
{
    public function __construct(
        private RouterRuntimeInterface $router,
        private MiddlewarePipeline     $pipeline
    ) {}

    /**
     * Handle the request through middleware then router.
     */
    public function handle(Request $request) : ResponseInterface
    {
        return $this->pipeline->handle(
            request: $request,
            next   : fn(Request $req) : ResponseInterface => $this->router->resolve(request: $req)
        );
    }
}
