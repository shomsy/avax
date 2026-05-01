<?php

declare(strict_types=1);

namespace Avax\Components\HTTP\Security\System\Flows\VerifyCsrfToken;

use Avax\Components\HTTP\Security\System\Capabilities\Csrf\CsrfTokens;
use Avax\Components\Request\System\PublicSurface\ServerRequest;
use Avax\Components\Response\System\PublicSurface\Response;
use Closure;

final readonly class VerifyCsrfToken
{
    private const array SAFE_METHODS = ['GET', 'HEAD', 'OPTIONS'];

    public function __construct(
        private CsrfTokens $csrfTokens,
    ) {}

    public function handle(ServerRequest $request, Closure $next): mixed
    {
        if (in_array($request->getMethod(), self::SAFE_METHODS, true)) {
            return $next($request);
        }

        $token = $this->extractToken($request);

        if (! $this->csrfTokens->validateToken($token)) {
            return $this->createErrorResponse();
        }

        return $next($request);
    }

    private function extractToken(ServerRequest $request): ?string
    {
        $token = $request->headers()->get('X-CSRF-TOKEN')
            ?? $request->headers()->get('X-XSRF-TOKEN');

        if ($token) {
            return $token;
        }

        $body = $request->body()->parsed();

        return $body['_token'] ?? $body['_csrf_token'] ?? null;
    }

    private function createErrorResponse(): Response
    {
        $response = new Response;
        $response->withStatus(403);
        $response->body()->write(json_encode([
            'error' => 'CSRF_TOKEN_MISMATCH',
            'message' => 'The CSRF token is invalid or expired.',
        ]));

        return $response->withHeader('Content-Type', 'application/json');
    }
}
