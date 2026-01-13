<?php

declare(strict_types=1);

namespace Avax\HTTP\Security;

use Avax\HTTP\Request\Request;
use Avax\HTTP\Response\ResponseFactory;
use Closure;
use Psr\Http\Message\ResponseInterface;
use SensitiveParameter;

/**
 * Middleware to enforce CSRF token validation for incoming requests.
 *
 * Responsibilities:
 * - Skip validation for safe HTTP methods (e.g., GET, OPTIONS).
 * - Validate CSRF tokens for unsafe methods (e.g., POST, DELETE).
 * - Respond with a 403 error for invalid or expired tokens.
 */
class VerifyCsrfToken
{
    private const array SAFE_METHODS = ['HEAD', 'GET', 'OPTIONS'];

    public function __construct(
        #[SensitiveParameter] protected readonly CsrfTokenManager $csrfTokenManager,
        protected readonly ResponseFactory                        $responseFactory
    ) {}

    /**
     * Handles CSRF validation for incoming requests.
     *
     * @param Request $request The incoming request.
     * @param Closure $next    The next middleware in the pipeline.
     *
     * @throws \Exception
     * @throws \Exception
     */
    public function handle(Request $request, Closure $next) : ResponseInterface
    {
        if ($this->isSafeMethod(request: $request)) {
            return $next($request);
        }

        $token = $this->extractToken(request: $request);

        if (! $this->csrfTokenManager->validateToken(token: $token)) {
            return $this->createTokenMismatchResponse();
        }

        return $next($request);
    }

    /**
     * Determines if the request method is safe (e.g., GET, OPTIONS).
     *
     * @param Request $request The incoming request.
     *
     * @return bool True if the method is safe, false otherwise.
     */
    private function isSafeMethod(Request $request) : bool
    {
        return in_array(needle: $request->getMethod(), haystack: self::SAFE_METHODS, strict: true);
    }

    /**
     * Extracts the CSRF token from the request.
     *
     * @param Request $request The incoming request.
     *
     * @return string|null The extracted token.
     */
    private function extractToken(Request $request) : string|null
    {
        $headerToken = $request->getHeaderLine(name: 'X-CSRF-TOKEN');

        if ($headerToken !== '' && $headerToken !== '0') {
            return $headerToken;
        }

        if ($request->getHeaderLine(name: 'Content-Type') === 'application/json') {
            $data = json_decode(json: $request->getBody()->getContents(), associative: true);

            return $data['_token'] ?? null;
        }

        return $request->input(key: '_token');
    }

    /**
     * Generates a response for token mismatches.
     *
     * @return ResponseInterface A 403 response indicating CSRF validation failure.
     */
    private function createTokenMismatchResponse() : ResponseInterface
    {
        $response = $this->responseFactory->createResponse(code: 403);

        $response->getBody()->write(
            string: json_encode(
                value: [
                    'error' => [
                        'code'    => 'CSRF_TOKEN_MISMATCH',
                        'message' => 'The CSRF token is invalid, missing, or expired.',
                    ],
                ]
            )
        );

        return $response->withHeader(name: 'Content-Type', value: 'application/json');
    }
}
