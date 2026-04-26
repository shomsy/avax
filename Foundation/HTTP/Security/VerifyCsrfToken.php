<?php

declare(strict_types=1);

namespace Avax\HTTP\Security;

use Avax\HTTP\Request\ServerRequest\IncomingRequest\ServerRequest;
use Avax\HTTP\Response\ResponseFactory;
use Closure;
use Exception;
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
    protected readonly ResponseFactory  $responseFactory;
    protected readonly CsrfTokens $csrfTokens;

    public function __construct(
        #[SensitiveParameter] CsrfTokens $csrfTokens,
        ResponseFactory                        $responseFactory
    )
    {
        $this->csrfTokens = $csrfTokens;
        $this->responseFactory  = $responseFactory;
    }

    /**
     * Handles CSRF validation for incoming requests.
     *
     * @param ServerRequest $request The incoming request.
     * @param Closure $next    The next middleware in the pipeline.
     *
     * @throws Exception
     * @throws Exception
     */
    public function handle(ServerRequest $request, Closure $next) : ResponseInterface
    {
        if ($this->isSafeMethod(request: $request)) {
            return $next($request);
        }

        $token = $this->extractToken(request: $request);

        if (! $this->csrfTokens->validateToken(token: $token)) {
            return $this->createTokenMismatchResponse();
        }

        return $next($request);
    }

    /**
     * Determines if the request method is safe (e.g., GET, OPTIONS).
     *
     * @param ServerRequest $request The incoming request.
     *
     * @return bool True if the method is safe, false otherwise.
     */
    private function isSafeMethod(ServerRequest $request) : bool
    {
        return in_array(
            needle  : strtoupper(string: $request->getMethod()),
            haystack: self::SAFE_METHODS,
            strict  : true
        );
    }

    /**
     * Extracts the CSRF token from the request.
     *
     * @param ServerRequest $request The incoming request.
     *
     * @return string|null The extracted token.
     */
    private function extractToken(ServerRequest $request) : string|null
    {
        $headerToken = $request->getHeaderLine(name: 'X-CSRF-TOKEN');

        if ($headerToken !== '' && $headerToken !== '0') {
            return $headerToken;
        }

        $xsrfToken = $request->getHeaderLine(name: 'X-XSRF-TOKEN');

        if ($xsrfToken !== '' && $xsrfToken !== '0') {
            return $xsrfToken;
        }

        $contentType = strtolower(string: $request->getHeaderLine(name: 'Content-Type'));

        if (str_starts_with(haystack: $contentType, needle: 'application/json')) {
            $data = json_decode(json: $request->getBody()->getContents(), associative: true);

            if (is_array(value: $data)) {
                $jsonToken = $data['_csrf_token'] ?? $data['_token'] ?? null;

                return is_string(value: $jsonToken) && $jsonToken !== '' ? $jsonToken : null;
            }
        }

        $inputToken = $request->inputs()->get(key: '_csrf_token') ?? $request->inputs()->get(key: '_token');

        return is_string(value: $inputToken) && $inputToken !== '' ? $inputToken : null;
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

        return $response
            ->withHeader(name: 'Content-Type', value: 'application/json')
            ->withHeader(name: 'System-Control', value: 'no-store');
    }
}
