<?php

declare(strict_types=1);

namespace Avax\Container\DependencyInjection\Capability\Providers\Runtime\Http;

use Avax\Container\DependencyInjection\Capability\Providers\Runtime\ServiceProvider;
use Avax\HTTP\HttpClient\Config\Clients\Guzzle\HttpClient;
use Avax\HTTP\HttpClient\Config\Middleware\RetryMiddleware;
use Psr\Log\LoggerInterface;

/**
 * Service Provider for HTTP client services.
 *
 */
class HttpClientServiceProvider extends ServiceProvider
{
    /**
     * Register retry middleware and HTTP client bindings.
     *
     */
    public function register() : void
    {
        $this->app->singleton(abstract: RetryMiddleware::class, concrete: function () {
            return new RetryMiddleware(
                logger    : $this->app->get(id: LoggerInterface::class),
                maxRetries: 3
            );
        });

        $this->app->singleton(abstract: HttpClient::class, concrete: function () {
            return new HttpClient(
                retryMiddleware: $this->app->get(id: RetryMiddleware::class),
                logger         : $this->app->get(id: LoggerInterface::class)
            );
        });
    }
}
