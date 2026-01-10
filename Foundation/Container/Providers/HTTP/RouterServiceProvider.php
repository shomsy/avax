<?php

declare(strict_types=1);

namespace Avax\Container\Providers\HTTP;

use Avax\Container\Features\Operate\Boot\ServiceProvider;
use Avax\HTTP\Router\Kernel\RouterKernel;
use Avax\HTTP\Router\Router;
use Avax\HTTP\Router\Routing\HttpRequestRouter;
use Avax\HTTP\Router\Validation\RouteConstraintValidator;

/**
 * Service Provider for routing services.
 *
 * @see docs/Providers/HTTP/RouterServiceProvider.md#quick-summary
 */
class RouterServiceProvider extends ServiceProvider
{
    /**
     * Register router bindings, validator, kernel, and alias.
     *
     * @return void
     * @see docs/Providers/HTTP/RouterServiceProvider.md#method-register
     */
    public function register(): void
    {
        if ($this->app->has(HttpRequestRouter::class)) {
            return;
        }

        $this->app->singleton(abstract: RouteConstraintValidator::class, concrete: RouteConstraintValidator::class);

        $this->app->singleton(abstract: HttpRequestRouter::class, concrete: function () {
            return new HttpRequestRouter(
                constraintValidator: $this->app->get(RouteConstraintValidator::class)
            );
        });

        $this->app->singleton(abstract: Router::class, concrete: Router::class);
        $this->app->singleton(abstract: RouterKernel::class, concrete: RouterKernel::class);

        // Bind 'router' alias
        $this->app->singleton(abstract: 'router', concrete: function () {
            return $this->app->get(Router::class);
        });
    }
}
