<?php

declare(strict_types=1);

namespace Avax\HTTP\Middleware\CSRF;

use Avax\HTTP\Request\Request;
use Avax\HTTP\Response\ResponseFactory;
use Avax\HTTP\Security\CsrfTokenManager;

/**
 * `CsrfMiddleware` is a middleware that ensures CSRF token validation for specific HTTP methods.
 * The class is marked as `readonly` to ensure its properties are immutable after instantiation.
 */
readonly class CsrfMiddleware
{
    /**
     * Constructor initializes the CsrfMiddleware with a CSRF token manager and a response factory.
     *
     * @param CsrfTokenManager $csrfTokenManager The manager used for CSRF token validation.
     * @param ResponseFactory  $responseFactory  The factory used to create HTTP responses.
     */
    public function __construct(
        private CsrfTokenManager $csrfTokenManager,
        private ResponseFactory  $responseFactory,
    ) {}

    /**
     * Handles the incoming request and ensures that CSRF token validation is performed for certain HTTP methods.
     * If the token is invalid or absent, a 403 response is generated.
     *
     * @param Request  $request The incoming HTTP request object.
     * @param callable $next    The next middleware to be called.
     *
     * @return mixed Returns the next middleware response or a 403 response if CSRF validation fails.
     * @throws \Exception
     */
    public function handle(Request $request, callable $next) : mixed
    {
        // Only validate CSRF tokens for methods that can modify state
        if (in_array($request->getMethod(), ['POST', 'PUT', 'PATCH', 'DELETE'])) {
            // Retrieve the CSRF token from the request
            $token = $request->get(key: '_csrf_token');

            // If the token is invalid or missing, return a 403 Forbidden response
            if (! $this->csrfTokenManager->validateToken(token: $token)) {
                return $this->responseFactory->createResponse(code: 403, reasonPhrase: 'CSRF token validation failed');
            }
        }

        // Proceed to the next middleware if CSRF validation passes
        return $next($request);
    }
}