<?php

declare(strict_types=1);

namespace Avax\Container\Providers\HTTP;

use Avax\Container\Features\Operate\Boot\ServiceProvider;
use Avax\HTTP\Middleware\MiddlewareGroupResolver;
use Avax\HTTP\Middleware\MiddlewarePipeline;
use Avax\HTTP\Middleware\MiddlewareResolver;

/**
 * Service Provider for middleware infrastructure.
 *
 * @see docs_md/Providers/HTTP/MiddlewareServiceProvider.md#quick-summary
 */
class MiddlewareServiceProvider extends ServiceProvider
{
    /**
     * Register middleware pipeline and resolver services.
     *
     * @return void
     * @see docs_md/Providers/HTTP/MiddlewareServiceProvider.md#method-register
     */
    public function register() : void
    {
        $this->app->singleton(abstract: MiddlewarePipeline::class, concrete: MiddlewarePipeline::class);
        $this->app->singleton(abstract: MiddlewareResolver::class, concrete: MiddlewareResolver::class);
        $this->app->singleton(abstract: MiddlewareGroupResolver::class, concrete: MiddlewareGroupResolver::class);
    }
}
