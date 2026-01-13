<?php

declare(strict_types=1);

namespace Avax\HTTP\Middleware;

use Psr\Http\Message\RequestInterface;
use Psr\Http\Message\ResponseInterface;

/**
 * PSR-15 Middleware Interface
 *
 * Middleware can:
 * - Return a ResponseInterface to short-circuit the pipeline
 * - Call $handler->handle() to delegate to the next middleware
 * - Throw an exception (will be caught by Kernel)
 *
 * Middleware must NOT:
 * - Know about the Router or routing details
 * - Directly access controller logic
 * - Modify the request in ways that affect routing
 *
 * @internal
 */
interface MiddlewareInterface
{
    /**
     * Process an incoming server request.
     */
    public function process(RequestInterface $request, RequestHandlerInterface $handler): ResponseInterface;
}
