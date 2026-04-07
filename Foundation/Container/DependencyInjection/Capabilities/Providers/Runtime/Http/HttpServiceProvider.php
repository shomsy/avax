<?php

declare(strict_types=1);

namespace Avax\Container\DependencyInjection\Capabilities\Providers\Runtime\Http;

use Avax\Container\DependencyInjection\Capabilities\Providers\Runtime\ServiceProvider;
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
 */
class HttpServiceProvider extends ServiceProvider
{
    /**
     * Register PSR-7/17 response and stream bindings.
     *
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
