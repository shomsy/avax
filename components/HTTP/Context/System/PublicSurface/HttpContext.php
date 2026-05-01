<?php

declare(strict_types=1);

namespace Avax\Components\HTTP\Context\System\PublicSurface;

use Avax\Components\HTTP\Context\System\Capabilities\Globals\GlobalsProviderInterface;
use Psr\Http\Message\ServerRequestInterface;
use Psr\Http\Message\UriInterface;

/**
 * Read-only HTTP context backed by either a PSR-7 request or PHP globals.
 *
 * Provides mockable access to all PHP superglobals via
 * GlobalsProviderInterface for testability.
 */
final readonly class HttpContext implements HttpContextInterface
{
    public function __construct(
        private ?ServerRequestInterface $request,
        private GlobalsProviderInterface $globals,
    ) {
    }

    public function request(): ?ServerRequestInterface
    {
        return $this->request;
    }

    public function baseUrl(): string
    {
        $scheme = $this->scheme();
        $host   = $this->host();
        $port   = $this->port();

        $authority = $host;
        if ($port !== null && ! $this->isStandardPort($scheme, $port)) {
            $authority = $host . ':' . $port;
        }

        return sprintf('%s://%s', $scheme, $authority);
    }

    public function scheme(): string
    {
        $uri = $this->request?->getUri();
        if ($uri instanceof UriInterface && ($scheme = $uri->getScheme()) !== '') {
            return $scheme;
        }

        $server = $this->serverParams();

        return (! empty($server['HTTPS']) && $server['HTTPS'] !== 'off') ? 'https' : 'http';
    }

    public function host(): string
    {
        $uri = $this->request?->getUri();
        if ($uri instanceof UriInterface && ($host = $uri->getHost()) !== '') {
            return $host;
        }

        $server = $this->serverParams();

        return $server['HTTP_HOST'] ?? $server['SERVER_NAME'] ?? 'localhost';
    }

    public function serverParams(): array
    {
        return $this->request?->getServerParams() ?? $this->globals->server();
    }

    private function port(): ?int
    {
        $uri = $this->request?->getUri();
        if ($uri instanceof UriInterface && ($port = $uri->getPort()) !== null) {
            return $port;
        }

        $server = $this->serverParams();
        $value  = $server['SERVER_PORT'] ?? null;

        return ($value !== null && $value !== '') ? (int) $value : null;
    }

    private function isStandardPort(string $scheme, int $port): bool
    {
        return ($scheme === 'http' && $port === 80) || ($scheme === 'https' && $port === 443);
    }

    public function isSecure(): bool
    {
        return $this->scheme() === 'https';
    }

    public function clientIp(): ?string
    {
        $server = $this->serverParams();

        return $server['HTTP_CLIENT_IP']
            ?? $server['HTTP_X_FORWARDED_FOR']
            ?? $server['REMOTE_ADDR']
            ?? null;
    }

    public function userAgent(): ?string
    {
        return $this->request?->getHeaderLine('User-Agent')
            ?? $this->serverParams()['HTTP_USER_AGENT']
            ?? null;
    }

    public function authHeader(): ?string
    {
        return $this->request?->getHeaderLine('Authorization')
            ?? $this->serverParams()['HTTP_AUTHORIZATION']
            ?? $this->serverParams()['REDIRECT_HTTP_AUTHORIZATION']
            ?? null;
    }

    public function cookies(): array
    {
        return $this->request?->getCookieParams() ?? $this->globals->cookies();
    }

    public function query(): array
    {
        return $this->request?->getQueryParams() ?? $this->globals->query();
    }

    public function post(): array
    {
        $parsed = $this->request?->getParsedBody();
        if (is_array($parsed)) {
            return $parsed;
        }

        return $this->globals->post();
    }

    public function files(): array
    {
        return $this->request?->getUploadedFiles() ?? $this->globals->files();
    }
}
