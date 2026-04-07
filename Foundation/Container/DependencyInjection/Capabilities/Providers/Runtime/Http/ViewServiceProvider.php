<?php

declare(strict_types=1);

namespace Avax\Container\DependencyInjection\Capabilities\Providers\Runtime\Http;

use Avax\Container\DependencyInjection\Capabilities\Providers\Runtime\ServiceProvider;
use Avax\View\BladeTemplateEngine;

/**
 * Service Provider for view and template engine services.
 *
 */
class ViewServiceProvider extends ServiceProvider
{
    /**
     * Register Blade template engine and alias with configured paths.
     *
     */
    public function register() : void
    {
        $this->app->singleton(abstract: BladeTemplateEngine::class, concrete: function () {
            $config = $this->app->get(id: 'config');

            return new BladeTemplateEngine(
                viewsPath: $config->get(key: 'views.views_path') ?? $this->app->basePath('Presentation/Views'),
                cachePath: $config->get(key: 'views.cache_path') ?? $this->app->basePath('var/cache/views')
            );
        });

        // Alias 'view'
        $this->app->singleton(abstract: 'view', concrete: function () {
            return $this->app->get(id: BladeTemplateEngine::class);
        });
    }
}
