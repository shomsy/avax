<?php

declare(strict_types=1);

namespace Avax\Container\ServiceProviders\Providers;

use Avax\Container\ServiceProviders\ServiceProvider;
use Avax\HTTP\Request\Request;
use Avax\HTTP\Response\Classes\{Response, Stream, StreamFactory};
use Avax\HTTP\Response\ResponseFactory;
use Avax\HTTP\URI\UriBuilder;
use Psr\Http\Message\{ResponseFactoryInterface,
    ResponseInterface,
    ServerRequestInterface,
    StreamFactoryInterface,
    StreamInterface,
    UriInterface};
use RuntimeException;
use Throwable;

class HTTPServiceProvider extends ServiceProvider
{
    /**
     * Registers HTTP-related services in the container as singletons.
     *
     * - StreamInterface is configured to use a temporary stream.
     * - StreamFactoryInterface, ResponseFactoryInterface, and UriInterface are registered for dependency injection.
     * - ResponseInterface uses the configured StreamInterface.
     */
    #[\Override]
    public function register() : void
    {
        // Register StreamInterface with a temporary stream
        $this->dependencyInjector->singleton(abstract: StreamInterface::class, concrete: static function () : Stream {
            $streamResource = fopen(filename: 'php://temp', mode: 'rw+');
            if ($streamResource === false) {
                throw new RuntimeException(message: "Failed to create temporary stream.");
            }

            return new Stream(stream: $streamResource);
        });

        // Register StreamFactoryInterface
        $this->dependencyInjector->singleton(abstract: StreamFactoryInterface::class, concrete: StreamFactory::class);

        // Register ResponseInterface
        $this->dependencyInjector->singleton(abstract: ResponseInterface::class, concrete: function () : Response {
            try {
                $stream = $this->dependencyInjector->get(id: StreamInterface::class);
            } catch (Throwable $throwable) {
                throw new RuntimeException(
                    message : "Failed to resolve StreamInterface for ResponseInterface.",
                    code    : 0,
                    previous: $throwable
                );
            }

            return new Response(stream: $stream);
        });

        // Register ResponseFactoryInterface
        $this->dependencyInjector->singleton(abstract: ResponseFactoryInterface::class, concrete: function (
        ) : ResponseFactory {
            try {
                $streamFactory = $this->dependencyInjector->get(id: StreamFactoryInterface::class);
                $response      = $this->dependencyInjector->get(id: ResponseInterface::class);
            } catch (Throwable $throwable) {
                throw new RuntimeException(
                    message : "Failed to resolve dependencies for ResponseFactoryInterface.",
                    code    : 0,
                    previous: $throwable
                );
            }

            return new ResponseFactory(streamFactory: $streamFactory, response: $response);
        });


        // Response factory
        $this->dependencyInjector->singleton(
            abstract: ResponseFactory::class,
            concrete: static fn($container) => $container->get(ResponseFactoryInterface::class)
        );

        // Register UriInterface
        $this->dependencyInjector->singleton(
            abstract: UriInterface::class,
            concrete: static fn() : UriBuilder => UriBuilder::createFromString(
                uri: env(key: 'APP_URL', default: 'http://localhost')
            )
        );

        // Request
        $this->dependencyInjector->singleton(
            abstract: ServerRequestInterface::class,
            concrete: static fn($container) : Request => Request::createFromGlobals()
        );

        // Request facade
        $this->dependencyInjector->singleton(
            abstract: 'Request',
            concrete: fn() : mixed => $this->dependencyInjector->get(id: ServerRequestInterface::class)
        );
    }

    /**
     * Placeholder for HTTP service bootstrapping logic.
     */
    #[\Override]
    public function boot() : void {}

}
