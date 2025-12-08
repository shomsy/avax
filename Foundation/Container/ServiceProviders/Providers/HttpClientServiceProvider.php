<?php

declare(strict_types=1);

namespace Avax\Container\ServiceProviders\Providers;

use Avax\Container\ServiceProviders\ServiceProvider;
use Avax\HTTP\HttpClient\Config\Clients\Guzzle\GuzzleClient;
use Avax\HTTP\HttpClient\Config\Clients\Guzzle\HttpClient;
use Avax\HTTP\HttpClient\Config\Middleware\Policies\Concrats\RetryPolicyInterface;
use Avax\HTTP\HttpClient\Config\Middleware\Policies\ServerErrorRetryPolicy;
use Avax\HTTP\HttpClient\Config\Middleware\RetryMiddleware;
use Avax\HTTP\Response\ResponseFactory;
use GuzzleHttp\Client;
use GuzzleHttp\HandlerStack;
use Psr\Http\Message\UriInterface;
use Psr\Log\LoggerInterface;

/**
 * Class HttpClientServiceProvider
 *
 * Registers HTTP client-related services in the container.
 */
class HttpClientServiceProvider extends ServiceProvider
{
    /**
     * Registers the necessary services into the service container.
     */
    public function register() : void
    {
        $this->dependencyInjector->singleton(
            abstract: RetryPolicyInterface::class,
            concrete: static fn() : ServerErrorRetryPolicy => new ServerErrorRetryPolicy() // TODO: double check
        );

        // Register RetryMiddleware
        $this->dependencyInjector->singleton(
            abstract: RetryMiddleware::class,
            concrete: fn() : RetryMiddleware => new RetryMiddleware(
                logger    : $this->dependencyInjector->get(LoggerInterface::class),
                maxRetries: 3, // Configurable retry wait time in ms
            )
        );

        // Register HttpClient
        $this->dependencyInjector->singleton(
            abstract: HttpClient::class,
            concrete: fn() : HttpClient => new HttpClient(
                retryMiddleware: $this->dependencyInjector->get(RetryMiddleware::class),
                logger         : $this->dependencyInjector->get(LoggerInterface::class),
                baseUri        : $this->dependencyInjector->get(UriInterface::class) // Inject base API URL
            )
        );

        // Register GuzzleClient
        $this->dependencyInjector->singleton(
            abstract: GuzzleClient::class,
            concrete: fn() : GuzzleClient => new GuzzleClient(
                httpClient     : $this->dependencyInjector->get(HttpClient::class),
                dataLogger     : $this->dependencyInjector->get(LoggerInterface::class),
                responseFactory: $this->dependencyInjector->get(ResponseFactory::class)
            )
        );

        // Optional: Register Guzzle HandlerStack
        $this->dependencyInjector->singleton(
            abstract: HandlerStack::class,
            concrete: static fn() : HandlerStack => HandlerStack::create()
        );

        // Optional: Register Guzzle Client
        $this->dependencyInjector->singleton(
            abstract: Client::class,
            concrete: fn() : Client => new Client(
                ['handler' => $this->dependencyInjector->get(HandlerStack::class)]
            )
        );
    }

    /**
     * Starts the boot process for the class.
     */
    public function boot() : void
    {
        // Optionally add bootstrapping logic if necessary
        // This could include preloading configurations or resolving dependencies
    }
}
