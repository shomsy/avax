<?php

declare(strict_types=1);

namespace Avax\Examples\GoldenPathRuntimeApp\HTTP;

use Avax\Components\HTTP\Middleware\System\PublicSurface\MiddlewareInterface;
use Avax\Components\HTTP\Request\System\PublicSurface\RequestInterface;
use Avax\Components\HTTP\Response\System\PublicSurface\ResponseInterface;

/**
 * Injects a correlation ID into every request and response.
 *
 * If the client provides an X-Correlation-ID header, it is reused.
 * Otherwise a new ID is generated. The ID is available for observability
 * and debugging across the full request lifecycle.
 */
final class CorrelationIdMiddleware implements MiddlewareInterface
{
    public function handle(RequestInterface $request, callable $next) : ResponseInterface
    {
        $correlationId = $request->getHeaderLine('X-Correlation-ID')
            ?: bin2hex(random_bytes(8));

        /** @var ResponseInterface $response */
        $response = $next($request);

        return $response->withHeader('X-Correlation-ID', $correlationId);
    }
}
