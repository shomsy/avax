<?php
declare(strict_types=1);

namespace Avax\HTTP\Context;

use Psr\Http\Message\ServerRequestInterface;
use Psr\Http\Message\UriInterface;

/**
 * Default HTTP context implementation.
 *
 * Prefers Request-derived data and falls back to globals provider only when
 * a request is not available.
 */
final readonly class HttpContext implements HttpContextInterface
{
    public function __construct(
        private ServerRequestInterface|null $request,
        private GlobalsProviderInterface    $globals
    ) {}

    public function request() : ServerRequestInterface|null
    {
        return $this->request;
    }

    public function baseUrl() : string
    {
        $scheme = $this->scheme();
        $host   = $this->host();
        $port   = $this->port();

        $authority = $host;
        if ($port !== null && ! $this->isStandardPort(scheme: $scheme, port: $port)) {
            if (! str_contains($host, ':')) {
                $authority = $host . ':' . $port;
            }
        }

        return sprintf('%s://%s', $scheme, $authority);
    }

    public function scheme() : string
    {
        $uri    = $this->request?->getUri();
        $scheme = $uri instanceof UriInterface ? $uri->getScheme() : '';
        if ($scheme !== '') {
            return $scheme;
        }

        $server = $this->serverParams();

        return (! empty($server['HTTPS']) && $server['HTTPS'] !== 'off') ? 'https' : 'http';
    }

    public function serverParams() : array
    {
        return $this->request?->getServerParams() ?? $this->globals->server();
    }

    public function host() : string
    {
        $uri  = $this->request?->getUri();
        $host = $uri instanceof UriInterface ? $uri->getHost() : '';
        if ($host !== '') {
            return $host;
        }

        $server = $this->serverParams();

        return $server['HTTP_HOST'] ?? $server['SERVER_NAME'] ?? 'localhost';
    }

    private function port() : int|null
    {
        $uri  = $this->request?->getUri();
        $port = $uri instanceof UriInterface ? $uri->getPort() : null;
        if ($port !== null) {
            return $port;
        }

        $server = $this->serverParams();
        $value  = $server['SERVER_PORT'] ?? null;
        if ($value === null || $value === '') {
            return null;
        }

        $port = (int) $value;

        return $port > 0 ? $port : null;
    }

    private function isStandardPort(string $scheme, int $port) : bool
    {
        return ($scheme === 'http' && $port === 80) || ($scheme === 'https' && $port === 443);
    }

    public function isSecure() : bool
    {
        return $this->scheme() === 'https';
    }

    public function clientIp() : string|null
    {
        $server = $this->serverParams();

        if (! empty($server['HTTP_CLIENT_IP'])) {
            return (string) $server['HTTP_CLIENT_IP'];
        }

        if (! empty($server['HTTP_X_FORWARDED_FOR'])) {
            $parts = explode(',', (string) $server['HTTP_X_FORWARDED_FOR']);
            $first = trim((string) ($parts[0] ?? ''));

            return $first !== '' ? $first : null;
        }

        $remote = $server['REMOTE_ADDR'] ?? null;

        return $remote !== null && $remote !== '' ? (string) $remote : null;
    }

    public function userAgent() : string|null
    {
        $agent = $this->request?->getHeaderLine(name: 'User-Agent') ?? '';
        if ($agent !== '') {
            return $agent;
        }

        $server = $this->serverParams();
        $agent  = $server['HTTP_USER_AGENT'] ?? null;

        return $agent !== null && $agent !== '' ? (string) $agent : null;
    }

    public function authHeader() : string|null
    {
        $header = $this->request?->getHeaderLine(name: 'Authorization') ?? '';
        if ($header !== '') {
            return $header;
        }

        $server = $this->serverParams();
        $header = $server['HTTP_AUTHORIZATION'] ?? $server['REDIRECT_HTTP_AUTHORIZATION'] ?? null;

        return $header !== null && $header !== '' ? (string) $header : null;
    }

    public function cookies() : array
    {
        return $this->request?->getCookieParams() ?? $this->globals->cookies();
    }
}
