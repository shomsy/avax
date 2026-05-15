<?php

declare(strict_types=1);

namespace Avax\Components\HTTP\System\Capabilities\MiddlewarePipeline;

use Avax\Components\HTTP\Request\System\PublicSurface\RequestInterface;
use Avax\Components\HTTP\Response\System\PublicSurface\Response;
use Avax\Components\HTTP\Response\System\PublicSurface\ResponseInterface;

/**
 * Base class for middleware that restricts access by IP address.
 */
abstract class IpRestrictionMiddleware implements MiddlewareInterface
{
    public function handle(RequestInterface $request, callable $next): ResponseInterface
    {
        $ip = $request->getServerParams()['REMOTE_ADDR'] ?? '0.0.0.0';

        if (! $this->isAllowedIp($ip)) {
            return $this->createForbiddenResponse();
        }

        return $next($request);
    }

    abstract protected function isAllowedIp(string $ipAddress): bool;

    protected function createForbiddenResponse(): ResponseInterface
    {
        return new Response(403, ['Content-Type' => ['text/plain']], 'Forbidden');
    }
}
