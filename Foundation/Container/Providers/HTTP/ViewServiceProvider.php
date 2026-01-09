<?php

declare(strict_types=1);

namespace Avax\Container\Providers\HTTP;

use Avax\Container\Features\Operate\Boot\ServiceProvider;
use Avax\View\BladeTemplateEngine;

/**
 * Service Provider for view and template engine services.
 *
 * @see docs_md/Providers/HTTP/ViewServiceProvider.md#quick-summary
 */
class ViewServiceProvider extends ServiceProvider
{
    /**
     * Register Blade template engine and alias with configured paths.
     *
     * @return void
     * @see docs_md/Providers/HTTP/ViewServiceProvider.md#method-register
     */
    public function register() : void
    {
        $this->app->singleton(abstract: BladeTemplateEngine::class, concrete: function () {
            $config = $this->app->get('config');

            return new BladeTemplateEngine(
                viewsPath: $config->get('views.views_path') ?? $this->app->basePath('Presentation/Views'),
                cachePath: $config->get('views.cache_path') ?? $this->app->basePath('var/cache/views')
            );
        });

        // Alias 'view'
        $this->app->singleton(abstract: 'view', concrete: function () {
            return $this->app->get(BladeTemplateEngine::class);
        });
    }
}
