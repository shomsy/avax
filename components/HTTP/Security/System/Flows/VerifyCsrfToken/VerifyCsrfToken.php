<?php

declare(strict_types=1);

namespace Avax\Components\HTTP\Security\System\Flows\VerifyCsrfToken;

use Avax\Components\HTTP\Request\System\PublicSurface\RequestInterface;
use Avax\Components\HTTP\Response\System\PublicSurface\Response;
use Avax\Components\HTTP\Security\System\Capabilities\Csrf\CsrfTokens;
use Closure;
use GuzzleHttp\Psr7\Utils;

final readonly class VerifyCsrfToken
{
    private const array SAFE_METHODS = ['GET', 'HEAD', 'OPTIONS'];

    public function __construct(
        private CsrfTokens $csrfTokens,
    ) {}

    public function handle(RequestInterface $serverRequest, Closure $next) : mixed
    {
        if (in_array($serverRequest->getMethod(), self::SAFE_METHODS, true)) {
            return $next($serverRequest);
        }

        $token = $this->extractToken($serverRequest);

        if (! $this->csrfTokens->validateToken($token)) {
            return $this->createErrorResponse();
        }

        return $next($serverRequest);
    }

    private function extractToken(RequestInterface $serverRequest) : ?string
    {
        $token = $serverRequest->getHeaderLine('X-CSRF-TOKEN')
            ?: $serverRequest->getHeaderLine('X-XSRF-TOKEN');

        if ($token) {
            return $token;
        }

        $body = $serverRequest->getParsedBody();

        if (is_array($body)) {
            return (string) ($body['_token'] ?? $body['_csrf_token'] ?? '');
        }

        return null;
    }

    private function createErrorResponse() : Response
    {
        return (new Response(403, ['Content-Type' => 'application/json']))
            ->withBody(Utils::streamFor(json_encode([
                                                        'error'   => 'CSRF_TOKEN_MISMATCH',
                                                        'message' => 'The CSRF token is invalid or expired.',
                                                    ])));
    }
}
