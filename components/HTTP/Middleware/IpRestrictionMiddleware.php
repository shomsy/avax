<?php

declare(strict_types=1);

namespace Avax\Components\HTTP\Middleware;

use Avax\Components\HTTP\Request\System\PublicSurface\RequestInterface;
use Avax\Components\HTTP\Response\System\PublicSurface\ResponseInterface;

/**
 * Base class for middleware that restricts access by IP address.
 */
abstract class IpRestrictionMiddleware implements MiddlewareInterface
{
    public function handle(RequestInterface $request, callable $next) : ResponseInterface
    {
        $ip = $request->getServerParams()['REMOTE_ADDR'] ?? '0.0.0.0';

        if (! $this->isAllowedIp($ip)) {
            return $this->createForbiddenResponse();
        }

        return $next($request);
    }

    abstract protected function isAllowedIp(string $ipAddress) : bool;

    protected function createForbiddenResponse() : ResponseInterface
    {
        // Return a minimal 403 response
        $responseClass = class_exists('Avax\Components\HTTP\Response\System\PublicSurface\Response')
            ? 'Avax\Components\HTTP\Response\System\PublicSurface\Response'
            : null;

        if ($responseClass !== null) {
            return new $responseClass(403, ['Content-Type' => ['text/plain']], 'Forbidden');
        }

        // Fallback: anonymous class implementing ResponseInterface
        return new class () implements ResponseInterface {
            public function getStatusCode() : int
            {
                return 403;
            }

            public function withStatus(int $code, string $reasonPhrase = '') : self
            {
                return $this;
            }

            public function getReasonPhrase() : string
            {
                return 'Forbidden';
            }

            public function getProtocolVersion() : string
            {
                return '1.1';
            }

            public function withProtocolVersion(string $version) : self
            {
                return $this;
            }

            public function getHeaders() : array
            {
                return ['Content-Type' => ['text/plain']];
            }

            public function hasHeader(string $name) : bool
            {
                return isset($this->getHeaders()[$name]);
            }

            public function getHeader(string $name) : array
            {
                return $this->getHeaders()[$name] ?? [];
            }

            public function getHeaderLine(string $name) : string
            {
                return implode(', ', $this->getHeader($name));
            }

            public function withHeader(string $name, $value) : self
            {
                return $this;
            }

            public function withAddedHeader(string $name, $value) : self
            {
                return $this;
            }

            public function withoutHeader(string $name) : self
            {
                return $this;
            }

            public function getBody() : mixed
            {
                return 'Forbidden';
            }

            public function withBody(mixed $body) : self
            {
                return $this;
            }
        };
    }
}
