<?php

declare(strict_types=1);

namespace Avax\Components\HTTP\System\Configuration;

/**
 * Top-level HTTP component configuration.
 *
 * Aggregates router, middleware, response, and request settings
 * into a single immutable configuration object.
 */
final readonly class HttpConfiguration
{
    /**
     * @param string               $baseUrl             Application base URL
     * @param bool                 $trustProxyHeaders   Whether to trust X-Forwarded-* headers
     * @param array<string>        $trustedProxies      List of trusted proxy IP addresses
     * @param int                  $maxRequestBodyBytes Maximum allowed request body size
     * @param string               $defaultLocale       Default locale for content negotiation
     * @param array<string, mixed> $router              Router-specific configuration
     * @param array<string, mixed> $middleware          Middleware-specific configuration
     * @param array<string, mixed> $response            Response-specific configuration
     */
    public function __construct(
        private string $baseUrl = 'http://localhost',
        private bool  $trustProxyHeaders = false,
        private array $trustedProxies = [],
        private int   $maxRequestBodyBytes = 1048576,
        private string $defaultLocale = 'en',
        private array $router = [],
        private array $middleware = [],
        private array $response = [],
    ) {}

    public function baseUrl() : string
    {
        return $this->baseUrl;
    }

    public function trustProxyHeaders() : bool
    {
        return $this->trustProxyHeaders;
    }

    /**
     * @return array<string>
     */
    public function trustedProxies() : array
    {
        return $this->trustedProxies;
    }

    /**
     * Check if a proxy IP is trusted.
     */
    public function isTrustedProxy(string $ip) : bool
    {
        return in_array($ip, $this->trustedProxies, true);
    }

    public function maxRequestBodyBytes() : int
    {
        return $this->maxRequestBodyBytes;
    }

    public function defaultLocale() : string
    {
        return $this->defaultLocale;
    }

    /**
     * @return array<string, mixed>
     */
    public function router() : array
    {
        return $this->router;
    }

    /**
     * @return array<string, mixed>
     */
    public function middleware() : array
    {
        return $this->middleware;
    }

    /**
     * @return array<string, mixed>
     */
    public function response() : array
    {
        return $this->response;
    }

    /**
     * Create a new configuration with merged overrides.
     *
     * @param array<string, mixed> $overrides
     */
    public function with(array $overrides) : self
    {
        return new self(
            baseUrl            : $overrides['base_url'] ?? $this->baseUrl,
            trustProxyHeaders  : $overrides['trust_proxy_headers'] ?? $this->trustProxyHeaders,
            trustedProxies     : $overrides['trusted_proxies'] ?? $this->trustedProxies,
            maxRequestBodyBytes: $overrides['max_request_body_bytes'] ?? $this->maxRequestBodyBytes,
            defaultLocale      : $overrides['default_locale'] ?? $this->defaultLocale,
            router             : $overrides['router'] ?? $this->router,
            middleware         : $overrides['middleware'] ?? $this->middleware,
            response           : $overrides['response'] ?? $this->response,
        );
    }

    /**
     * Create configuration from an array.
     *
     * @param array<string, mixed> $config
     */
    public static function fromArray(array $config) : self
    {
        return new self(
            baseUrl            : $config['base_url'] ?? 'http://localhost',
            trustProxyHeaders  : $config['trust_proxy_headers'] ?? false,
            trustedProxies     : $config['trusted_proxies'] ?? [],
            maxRequestBodyBytes: $config['max_request_body_bytes'] ?? 1048576,
            defaultLocale      : $config['default_locale'] ?? 'en',
            router             : $config['router'] ?? [],
            middleware         : $config['middleware'] ?? [],
            response           : $config['response'] ?? [],
        );
    }
}
