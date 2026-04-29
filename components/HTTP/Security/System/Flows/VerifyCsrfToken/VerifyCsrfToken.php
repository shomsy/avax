<?php
declare(strict_types=1);

namespace Avax\Components\HTTP\Security\System\Flows\VerifyCsrfToken;

use Avax\Components\HTTP\Security\System\Capabilities\Csrf\CsrfTokens;
use Avax\Components\HTTP\Request\System\PublicSurface\RequestInterface;
use Closure;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ResponseFactoryInterface;

final readonly class VerifyCsrfToken
{
    private const array SAFE_METHODS = ['HEAD', 'GET', 'OPTIONS'];

    public function __construct(
        private CsrfTokens $csrfTokens,
        private ResponseFactoryInterface $responseFactory
    ) {
    }

    public function handle(RequestInterface $request, Closure $next): ResponseInterface
    {
        if (in_array(strtoupper($request->getMethod()), self::SAFE_METHODS, true)) {
            return $next($request);
        }

        $token = $this->extractToken($request);

        if (!$this->csrfTokens->validateToken($token)) {
            return $this->createTokenMismatchResponse();
        }

        return $next($request);
    }

    private function extractToken(RequestInterface $request): ?string
    {
        // Try headers
        $token = $request->getHeaderLine('X-CSRF-TOKEN');
        if ($token !== '') return $token;

        $token = $request->getHeaderLine('X-XSRF-TOKEN');
        if ($token !== '') return $token;

        // Try body/query
        $inputs = $request->all();
        $token = $inputs['_csrf_token'] ?? $inputs['_token'] ?? null;

        return is_string($token) && $token !== '' ? $token : null;
    }

    private function createTokenMismatchResponse(): ResponseInterface
    {
        $response = $this->responseFactory->createResponse(403);
        $response->getBody()->write(json_encode([
            'error' => [
                'code' => 'CSRF_TOKEN_MISMATCH',
                'message' => 'The CSRF token is invalid, missing, or expired.',
            ],
        ]));

        return $response
            ->withHeader('Content-Type', 'application/json')
            ->withHeader('Cache-Control', 'no-store');
    }
}
