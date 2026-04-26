<?php

declare(strict_types=1);

namespace Avax\HTTP\Middleware\CSRF;

use Avax\HTTP\Request\ServerRequest\IncomingRequest\ServerRequest;
use Avax\HTTP\Response\ResponseFactory;
use Avax\HTTP\Security\CsrfTokens;
use Exception;
use SensitiveParameter;

/**
 * `CsrfMiddleware` is a middleware that ensures CSRF token validation for specific HTTP methods.
 * The class is marked as `readonly` to ensure its properties are immutable after instantiation.
 */
readonly class CsrfMiddleware
{
    private ResponseFactory  $responseFactory;
    private CsrfTokens $csrfTokens;

    /**
     * Constructor initializes the CsrfMiddleware with a CSRF tokens manager and a response factory.
     *
     * @param CsrfTokens $csrfTokens The store used for CSRF token validation.
     * @param ResponseFactory  $responseFactory  The factory used to create HTTP responses.
     */
    public function __construct(
        #[SensitiveParameter] CsrfTokens $csrfTokens,
        ResponseFactory                        $responseFactory,
    )
    {
        $this->csrfTokens = $csrfTokens;
        $this->responseFactory  = $responseFactory;
    }

    /**
     * Handles the incoming request and ensures that CSRF token validation is performed for certain HTTP methods.
     * If the token is invalid or absent, a 403 response is generated.
     *
     * @param Request  $request The incoming HTTP request object.
     * @param callable $next    The next middleware to be called.
     *
     * @return mixed Returns the next middleware response or a 403 response if CSRF validation fails.
     *
     * @throws Exception
     */
    public function handle(ServerRequest $request, callable $next) : mixed
    {
        // Only validate CSRF tokens for methods that can modify state
        if (in_array(needle: $request->getMethod(), haystack: ['POST', 'PUT', 'PATCH', 'DELETE'])) {
            // Retrieve the CSRF token from the request
            $token = $request->inputs()->get(key: '_csrf_token');

            // If the token is invalid or missing, return a 403 Forbidden response
            if (! $this->csrfTokens->validateToken(token: is_string(value: $token) ? $token : null)) {
                return $this->responseFactory->createResponse(code: 403, reasonPhrase: 'CSRF token validation failed');
            }
        }

        // Proceed to the next middleware if CSRF validation passes
        return $next($request);
    }
}
