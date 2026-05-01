<?php

declare(strict_types=1);

namespace Avax\Components\HTTP\Client\System\Capabilities\Requests;

use Avax\Components\HTTP\Client\System\Capabilities\Resilience\RetryPolicy;
use Avax\Components\HTTP\Client\System\Capabilities\Resilience\TimeoutPolicy;

/**
 * RequestOptions - Configuration options for HTTP requests.
 *
 * Immutable DTO that holds configurable options for outbound requests
 * including timeouts, SSL verification, proxy settings, and retry policies.
 */
final readonly class RequestOptions
{
    /** Default timeout in milliseconds (30 seconds). */
    public const DEFAULT_TIMEOUT = 30_000;

    /** Default connect timeout in milliseconds (10 seconds). */
    public const DEFAULT_CONNECT_TIMEOUT = 10_000;

    /**
     * @param int                  $timeout         Total request timeout in milliseconds
     * @param int                  $connectTimeout  Connection timeout in milliseconds
     * @param bool                 $verifySsl       Whether to verify SSL certificates
     * @param string|null          $sslCertPath     Path to SSL certificate file
     * @param string|null          $sslKeyPath      Path to SSL key file
     * @param string|null          $proxy           Proxy URL (e.g., http://proxy:8080)
     * @param string|null          $proxyAuth       Proxy authentication string
     * @param bool                 $followRedirects Whether to follow redirects
     * @param int                  $maxRedirects    Maximum number of redirects to follow
     * @param bool                 $httpErrors      Whether to throw on HTTP error status codes
     * @param string|null          $encoding        Request body encoding
     * @param RetryPolicy|null     $retryPolicy     Retry policy for failed requests
     * @param TimeoutPolicy|null   $timeoutPolicy   Timeout policy configuration
     * @param array<string, mixed> $additional      Additional custom options
     */
    public function __construct(
        public int              $timeout = self::DEFAULT_TIMEOUT,
        public int              $connectTimeout = self::DEFAULT_CONNECT_TIMEOUT,
        public bool             $verifySsl = true,
        public string|null      $sslCertPath = null,
        public string|null      $sslKeyPath = null,
        public string|null      $proxy = null,
        public string|null      $proxyAuth = null,
        public bool             $followRedirects = true,
        public int              $maxRedirects = 5,
        public bool             $httpErrors = false,
        public string|null      $encoding = null,
        public RetryPolicy|null $retryPolicy = null,
        public TimeoutPolicy|null $timeoutPolicy = null,
        public array            $additional = [],
    ) {}

    /**
     * Create options with a custom timeout.
     */
    public static function withTimeout(int $timeoutMs) : self
    {
        return new self(timeout: $timeoutMs);
    }

    /**
     * Create options with SSL verification disabled.
     */
    public static function insecure() : self
    {
        return new self(verifySsl: false);
    }

    /**
     * Create options with a retry policy.
     */
    public static function withRetry(RetryPolicy $retryPolicy) : self
    {
        return new self(retryPolicy: $retryPolicy);
    }

    /**
     * Create options with a proxy.
     */
    public static function withProxy(string $proxyUrl, string $auth = null) : self
    {
        return new self(proxy: $proxyUrl, proxyAuth: $auth);
    }

    /**
     * Create options without following redirects.
     */
    public static function noRedirects() : self
    {
        return new self(followRedirects: false);
    }

    /**
     * Merge these options with another set.
     * Values from the other options override these where set.
     */
    public function merge(self $other) : self
    {
        return new self(
            timeout        : $other->timeout !== self::DEFAULT_TIMEOUT ? $other->timeout : $this->timeout,
            connectTimeout : $other->connectTimeout !== self::DEFAULT_CONNECT_TIMEOUT ? $other->connectTimeout : $this->connectTimeout,
            verifySsl      : $other->verifySsl !== true ? $other->verifySsl : $this->verifySsl,
            sslCertPath    : $other->sslCertPath ?? $this->sslCertPath,
            sslKeyPath     : $other->sslKeyPath ?? $this->sslKeyPath,
            proxy          : $other->proxy ?? $this->proxy,
            proxyAuth      : $other->proxyAuth ?? $this->proxyAuth,
            followRedirects: $other->followRedirects !== true ? $other->followRedirects : $this->followRedirects,
            maxRedirects   : $other->maxRedirects !== 5 ? $other->maxRedirects : $this->maxRedirects,
            httpErrors     : $other->httpErrors !== false ? $other->httpErrors : $this->httpErrors,
            encoding       : $other->encoding ?? $this->encoding,
            retryPolicy    : $other->retryPolicy ?? $this->retryPolicy,
            timeoutPolicy  : $other->timeoutPolicy ?? $this->timeoutPolicy,
            additional     : array_merge($this->additional, $other->additional),
        );
    }

    /**
     * Get the effective timeout from the timeout policy if set, otherwise the timeout value.
     */
    public function effectiveTimeout() : int
    {
        return $this->timeoutPolicy?->timeoutMs ?? $this->timeout;
    }

    /**
     * Get an additional option by key.
     */
    public function get(string $key, mixed $default = null) : mixed
    {
        return $this->additional[$key] ?? $default;
    }

    /**
     * Check if retries are configured.
     */
    public function hasRetry() : bool
    {
        return $this->retryPolicy !== null;
    }
}
