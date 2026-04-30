<?php

declare(strict_types=1);

namespace Avax\Components\HTTP\Client\System\Configuration;

use Avax\Components\HTTP\Client\System\Capabilities\Middleware\ClientMiddlewareInterface;
use Avax\Components\HTTP\Client\System\Capabilities\Requests\RequestOptions;
use Avax\Components\HTTP\Client\System\Capabilities\Resilience\RetryPolicy;
use Avax\Components\HTTP\Client\System\Capabilities\Resilience\TimeoutPolicy;
use Avax\Components\HTTP\Client\System\Capabilities\Transports\CurlTransport;
use Avax\Components\HTTP\Client\System\Capabilities\Transports\HttpTransportInterface;
use Avax\Components\HTTP\Client\System\PublicSurface\HttpClient;

/**
 * HttpClientProvider - Provider for configured HTTP client instances.
 *
 * Centralizes HTTP client configuration and provides factory methods
 * for creating pre-configured HttpClient instances for different use cases.
 *
 * Usage:
 *   $provider = new HttpClientProvider();
 *   $client = $provider->client(baseUrl: 'https://api.example.com');
 *
 *   // Or use static factories:
 *   $client = HttpClientProvider::forApi('https://api.example.com');
 *   $client = HttpClientProvider::forWebhooks();
 *   $client = HttpClientProvider::forFileDownloads();
 */
final class HttpClientProvider
{
    /**
     * @var array<string, HttpClient>
     */
    private array $clients = [];

    /**
     * @var array<string, mixed>
     */
    private array $config = [];

    /**
     * @param array<string, mixed> $config Default configuration
     */
    public function __construct(array $config = [])
    {
        $this->config = $config;
    }

    /**
     * Create a client optimized for API calls.
     *
     * Features: JSON content type, moderate timeout, retry on server errors.
     */
    public static function forApi(
        string $baseUrl,
        int $timeoutMs = 15000,
        int $retryAttempts = 3,
    ) : HttpClient
    {
        $options = new RequestOptions(
            timeout    : $timeoutMs,
            retryPolicy: RetryPolicy::exponential(
                             attempts   : $retryAttempts,
                             baseDelayMs: 500,
                             maxDelayMs : 5000,
                         ),
            httpErrors : true,
        );

        return new HttpClient(
            baseUrl       : $baseUrl,
            defaultOptions: $options,
        );
    }

    /**
     * Create a client optimized for webhook delivery.
     *
     * Features: Short timeout, aggressive retries, no redirect following.
     */
    public static function forWebhooks(
        int $timeoutMs = 5000,
        int $retryAttempts = 5,
    ) : HttpClient
    {
        $options = new RequestOptions(
            timeout        : $timeoutMs,
            connectTimeout : 2000,
            followRedirects: false,
            retryPolicy    : RetryPolicy::exponential(
                                 attempts         : $retryAttempts,
                                 baseDelayMs      : 1000,
                                 backoffMultiplier: 3.0,
                                 maxDelayMs       : 15000,
                             ),
            httpErrors     : true,
        );

        return new HttpClient(
            defaultOptions: $options,
        );
    }

    /**
     * Create a client optimized for file downloads.
     *
     * Features: Long timeout, no retries (downloads are typically large).
     */
    public static function forFileDownloads(
        int $timeoutMs = 300000,
    ) : HttpClient
    {
        $options = new RequestOptions(
            timeout        : $timeoutMs,
            connectTimeout : 30000,
            retryPolicy    : RetryPolicy::none(),
            followRedirects: true,
            maxRedirects   : 10,
        );

        return new HttpClient(
            defaultOptions: $options,
        );
    }

    /**
     * Create a client with strict timeouts.
     */
    public static function withStrictTimeouts(
        string $baseUrl = null,
        int    $timeoutMs = 3000,
    ) : HttpClient
    {
        $options = new RequestOptions(
            timeout       : $timeoutMs,
            connectTimeout: 1000,
            timeoutPolicy : TimeoutPolicy::strict($timeoutMs),
            retryPolicy   : RetryPolicy::none(),
        );

        return new HttpClient(
            baseUrl       : $baseUrl,
            defaultOptions: $options,
        );
    }

    /**
     * Create a client with middleware.
     *
     * @param ClientMiddlewareInterface ...$middlewares
     */
    public static function withMiddleware(
        string $baseUrl = null,
        ClientMiddlewareInterface ...$middlewares,
    ) : HttpClient
    {
        return new HttpClient(
            baseUrl    : $baseUrl,
            middlewares: $middlewares,
        );
    }

    /**
     * Get or create an HTTP client with the given configuration.
     *
     * @param string|null                 $baseUrl   Base URL for requests
     * @param RequestOptions|null         $options   Default request options
     * @param HttpTransportInterface|null $transport Custom transport
     * @param array<ClientMiddlewareInterface> $middlewares Middleware to apply
     * @param string|null                 $name      Named client identifier (for caching)
     */
    public function client(
        string                 $baseUrl = null,
        RequestOptions         $options = null,
        HttpTransportInterface $transport = null,
        array                  $middlewares = [],
        string                 $name = null,
    ) : HttpClient
    {
        // Use name-based caching if a name is provided
        if ($name !== null && isset($this->clients[$name])) {
            return $this->clients[$name];
        }

        $resolvedOptions = $options ?? $this->resolveOptions();
        $resolvedTransport = $transport ?? new CurlTransport();

        $client = new HttpClient(
            baseUrl       : $baseUrl ?? $this->config['base_url'] ?? null,
            transport     : $resolvedTransport,
            defaultOptions: $resolvedOptions,
            middlewares   : $middlewares,
        );

        if ($name !== null) {
            $this->clients[$name] = $client;
        }

        return $client;
    }

    /**
     * Resolve default options from configuration.
     */
    private function resolveOptions() : RequestOptions
    {
        $timeout        = $this->config['timeout'] ?? RequestOptions::DEFAULT_TIMEOUT;
        $connectTimeout = $this->config['connect_timeout'] ?? RequestOptions::DEFAULT_CONNECT_TIMEOUT;
        $verifySsl      = $this->config['verify_ssl'] ?? true;
        $proxy          = $this->config['proxy'] ?? null;
        $followRedirects = $this->config['follow_redirects'] ?? true;
        $maxRedirects   = $this->config['max_redirects'] ?? 5;

        $retryPolicy = null;
        if ($this->config['retry_attempts'] ?? 0 > 0) {
            $retryPolicy = RetryPolicy::exponential(
                attempts   : (int) $this->config['retry_attempts'],
                baseDelayMs: (int) ($this->config['retry_delay'] ?? 1000),
            );
        }

        return new RequestOptions(
            timeout        : $timeout,
            connectTimeout : $connectTimeout,
            verifySsl      : $verifySsl,
            proxy          : $proxy,
            followRedirects: $followRedirects,
            maxRedirects   : $maxRedirects,
            retryPolicy    : $retryPolicy,
        );
    }

    /**
     * Get a cached client by name.
     */
    public function getCached(string $name) : ?HttpClient
    {
        return $this->clients[$name] ?? null;
    }

    /**
     * Clear all cached clients.
     */
    public function clearCache() : void
    {
        $this->clients = [];
    }

    /**
     * Get the current configuration.
     *
     * @return array<string, mixed>
     */
    public function getConfig() : array
    {
        return $this->config;
    }
}
