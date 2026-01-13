<?php

declare(strict_types=1);

namespace Avax\Container\Providers\HTTP;

use Avax\Container\Providers\ServiceProvider;
use Avax\HTTP\Dispatcher\ControllerDispatcher;
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
 * @see docs/Providers/HTTP/HTTPServiceProvider.md#quick-summary
 */
class HTTPServiceProvider extends ServiceProvider
{
    /**
     * Register PSR-7/17 response and stream bindings.
     *
     * @see docs/Providers/HTTP/HTTPServiceProvider.md#method-register
     */
    public function register() : void
    {
        // Core dispatcher for controllers (used by routing pipeline)
        $this->app->singleton(abstract: ControllerDispatcher::class, concrete: ControllerDispatcher::class);

        $this->app->singleton(abstract: StreamInterface::class, concrete: static function () {
            return new Stream(stream: fopen('php://temp', 'rw+'));
        });

        $this->app->singleton(abstract: StreamFactoryInterface::class, concrete: StreamFactory::class);

        $this->app->singleton(abstract: ResponseInterface::class, concrete: function () {
            return new Response(stream: $this->app->get(id: StreamInterface::class));
        });

        $this->app->singleton(abstract: ResponseFactoryInterface::class, concrete: ResponseFactory::class);
    }
}
