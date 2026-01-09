<?php

declare(strict_types=1);

namespace Avax\Container\Providers\HTTP;

use Avax\Container\Features\Operate\Boot\ServiceProvider;
use Avax\HTTP\Response\Classes\Response;
use Avax\HTTP\Response\Classes\Stream;
use Avax\HTTP\Response\Classes\StreamFactory;
use Avax\HTTP\Response\ResponseFactory;
use Psr\Http\Message\ResponseFactoryInterface;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\StreamFactoryInterface;
use Psr\Http\Message\StreamInterface;

/**
 * Service Provider for core HTTP components (PSR-7/17).
 *
 * @see docs_md/Providers/HTTP/HTTPServiceProvider.md#quick-summary
 */
class HTTPServiceProvider extends ServiceProvider
{
    /**
     * Register PSR-7/17 response and stream bindings.
     *
     * @return void
     * @see docs_md/Providers/HTTP/HTTPServiceProvider.md#method-register
     */
    public function register() : void
    {
        $this->app->singleton(abstract: StreamInterface::class, concrete: function () {
            return new Stream(stream: fopen('php://temp', 'rw+'));
        });

        $this->app->singleton(abstract: StreamFactoryInterface::class, concrete: StreamFactory::class);

        $this->app->singleton(abstract: ResponseInterface::class, concrete: function () {
            return new Response(stream: $this->app->get(StreamInterface::class));
        });

        $this->app->singleton(abstract: ResponseFactoryInterface::class, concrete: ResponseFactory::class);
    }
}
