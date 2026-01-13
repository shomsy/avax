<?php

declare(strict_types=1);

namespace Avax\HTTP\Middleware;

use Psr\Http\Message\RequestInterface;
use Psr\Http\Message\ResponseInterface;

/**
 * PSR-15 Request Handler Interface
 *
 * Handles an incoming server request and produces a response.
 * This interface replaces the callable-based middleware approach.
 *
 * @internal
 */
interface RequestHandlerInterface
{
    /**
     * Handle the request and return a response.
     */
    public function handle(RequestInterface $request): ResponseInterface;
}
