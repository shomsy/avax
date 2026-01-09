<?php

declare(strict_types=1);

namespace Avax\Container\Providers\HTTP;

use Avax\Container\Features\Operate\Boot\ServiceProvider;
use Avax\HTTP\HttpClient\Config\Clients\Guzzle\HttpClient;
use Avax\HTTP\HttpClient\Config\Middleware\RetryMiddleware;
use Psr\Log\LoggerInterface;

/**
 * Service Provider for HTTP client services.
 *
 * @see docs_md/Providers/HTTP/HttpClientServiceProvider.md#quick-summary
 */
class HttpClientServiceProvider extends ServiceProvider
{
    /**
     * Register retry middleware and HTTP client bindings.
     *
     * @return void
     * @see docs_md/Providers/HTTP/HttpClientServiceProvider.md#method-register
     */
    public function register() : void
    {
        $this->app->singleton(abstract: RetryMiddleware::class, concrete: function () {
            return new RetryMiddleware(
                logger    : $this->app->get(LoggerInterface::class),
                maxRetries: 3
            );
        });

        $this->app->singleton(abstract: HttpClient::class, concrete: function () {
            return new HttpClient(
                retryMiddleware: $this->app->get(RetryMiddleware::class),
                logger         : $this->app->get(LoggerInterface::class)
            );
        });
    }
}
