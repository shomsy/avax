<?php

declare(strict_types=1);

namespace Avax\Container\DependencyInjection\Capabilities\Providers\Runtime\Http;

use Avax\Container\DependencyInjection\Capabilities\Providers\Runtime\ServiceProvider;
use Avax\HTTP\Middleware\MiddlewareGroupResolver;
use Avax\HTTP\Middleware\MiddlewarePipeline;
use Avax\HTTP\Middleware\MiddlewareResolver;

/**
 * Service Provider for middleware infrastructure.
 *
 */
class MiddlewareServiceProvider extends ServiceProvider
{
    /**
     * Register middleware pipeline and resolver services.
     *
     */
    public function register() : void
    {
        $this->app->singleton(abstract: MiddlewarePipeline::class, concrete: MiddlewarePipeline::class);
        $this->app->singleton(abstract: MiddlewareResolver::class, concrete: MiddlewareResolver::class);
        $this->app->singleton(abstract: MiddlewareGroupResolver::class, concrete: MiddlewareGroupResolver::class);
    }
}
