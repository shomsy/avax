<?php

declare(strict_types=1);

namespace Avax\HTTP;

use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;

/**
 * HTTP Kernel - Orchestrator for HTTP Request Processing
 *
 * BC GUARANTEED: This interface defines the stable, public API for HTTP request processing.
 * All methods are guaranteed to be backward compatible in future versions.
 *
 * The Kernel is responsible for:
 * - Receiving PSR-7 ServerRequestInterface
 * - Delegating to Router for route resolution
 * - Building and executing middleware pipeline
 * - Handling exception boundary
 * - Returning PSR-7 ResponseInterface
 *
 * @api
 */
interface Kernel
{
    /**
     * Process an HTTP request and return an HTTP response.
     *
     * This is the single entry point for HTTP request processing.
     *
     * @param  ServerRequestInterface  $request  The HTTP request to process
     * @return ResponseInterface The HTTP response
     */
    public function handle(ServerRequestInterface $request): ResponseInterface;
}
