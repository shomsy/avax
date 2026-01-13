<?php

declare(strict_types=1);

namespace Avax\HTTP\Middleware;

use Avax\HTTP\Response\ResponseFactory;
use Psr\Http\Message\RequestInterface;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;

/**
 * PSR-15 Middleware for CSRF token verification.
 *
 * Validates CSRF tokens for state-changing requests (POST, PUT, DELETE, PATCH).
 * Tokens are expected to be provided via:
 * - X-CSRF-Token header
 * - _csrf_token request attribute
 * - csrf_token POST parameter
 */
readonly class CsrfVerificationMiddleware implements MiddlewareInterface
{
    public function __construct(
        private ResponseFactory $responseFactory,
        private string          $tokenAttribute = '_csrf_token'
    ) {}

    /**
     * PSR-15 process method: verify CSRF token before proceeding.
     *
     * @param RequestInterface        $request The incoming request.
     * @param RequestHandlerInterface $handler The next handler in the chain.
     *
     * @return ResponseInterface The response or 403 if CSRF verification fails.
     */
    public function process(RequestInterface $request, RequestHandlerInterface $handler) : ResponseInterface
    {
        // Skip CSRF verification for safe methods
        if ($this->isSafeMethod(method: $request->getMethod())) {
            return $handler->handle(request: $request);
        }

        // Extract CSRF token from request
        $token = $this->extractToken(request: $request);

        // Verify token (simplified - in real implementation, compare with session/token store)
        if (! $this->isValidToken(token: $token)) {
            return $this->createCsrfErrorResponse();
        }

        return $handler->handle(request: $request);
    }

    /**
     * Check if HTTP method is considered safe (doesn't need CSRF protection).
     */
    private function isSafeMethod(string $method) : bool
    {
        return in_array(strtoupper($method), ['GET', 'HEAD', 'OPTIONS'], true);
    }

    /**
     * Extract CSRF token from various sources.
     */
    private function extractToken(RequestInterface $request) : string|null
    {
        // Try header first
        if ($request->hasHeader(name: 'X-CSRF-Token')) {
            return $request->getHeaderLine(name: 'X-CSRF-Token');
        }

        // Try request attribute
        if ($request instanceof ServerRequestInterface) {
            $attribute = $request->getAttribute(attribute: $this->tokenAttribute);
            if ($attribute !== null) {
                return (string) $attribute;
            }

            // Try parsed body for POST requests
            $parsedBody = $request->getParsedBody();
            if (is_array($parsedBody) && isset($parsedBody['csrf_token'])) {
                return (string) $parsedBody['csrf_token'];
            }
        }

        return null;
    }

    /**
     * Validate CSRF token (simplified implementation).
     *
     * In real implementation, this would:
     * - Compare against session-stored token
     * - Check token expiration
     * - Use cryptographically secure comparison
     */
    private function isValidToken(string|null $token) : bool
    {
        if ($token === null || strlen($token) < 32) {
            return false;
        }

        // Simplified check - in real implementation, compare with session token
        // For testing purposes, accept any non-empty token of reasonable length
        return true;
    }

    /**
     * Create CSRF verification error response.
     */
    private function createCsrfErrorResponse() : ResponseInterface
    {
        return $this->responseFactory->createErrorResponse(
            statusCode: 403,
            message   : 'CSRF token verification failed'
        );
    }
}
