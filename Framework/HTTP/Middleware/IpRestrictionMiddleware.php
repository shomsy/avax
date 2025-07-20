<?php

declare(strict_types=1);

namespace Gemini\HTTP\Middleware;

use Closure;
use Gemini\HTTP\Request\Request;
use Gemini\HTTP\Response\ResponseFactory;
use Psr\Http\Message\ResponseInterface;

/**
 * Abstract base class for middleware that restricts access based on IP addresses.
 *
 * Concrete subclasses can define specific business logic for allowable IPs,
 * such as office IPs or other access-controlled networks.
 */
abstract class IpRestrictionMiddleware
{
    public function __construct(protected ResponseFactory $responseFactory) {}

    /**
     * Main entry point for IP restriction middleware.
     *
     * @param Request $request The incoming HTTP request.
     * @param Closure $next    The next middleware or request handler.
     *
     * @return ResponseInterface A response if IP is disallowed, or proceeds to the next middleware.
     */
    public function handle(Request $request, Closure $next) : ResponseInterface
    {
        if (! $this->isAllowedIp($request->getClientIp())) {
            return $this->createAccessDeniedResponse();
        }

        return $next($request);
    }

    /**
     * Checks if the IP address is allowed.
     *
     * @param string $ipAddress The IP address to check.
     *
     * @return bool True if the IP is allowed, false otherwise.
     */
    abstract protected function isAllowedIp(string $ipAddress) : bool;

    /**
     * Generates a 403 Forbidden response for disallowed IPs.
     *
     * @return ResponseInterface The access denied response.
     */
    protected function createAccessDeniedResponse() : ResponseInterface
    {
        return $this->responseFactory->view(
            template: 'errors.403',
            data    : ['message' => 'Access from your IP address is not allowed.'],
            status  : 403
        );
    }
}
