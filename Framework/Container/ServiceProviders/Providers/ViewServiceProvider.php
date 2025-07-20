<?php

declare(strict_types=1);

namespace Gemini\Container\ServiceProviders\Providers;

use Gemini\Container\ServiceProviders\ServiceProvider;
use Gemini\View\BladeTemplateEngine;
use Infrastructure\Config\Service\Config;

class ViewServiceProvider extends ServiceProvider
{
    public function register() : void
    {
//        $this->dependencyInjector->singleton(
//            abstract: TemplateEngine::class,
//            concrete: fn() => new TemplateEngine(
//                templatePath: $this->dependencyInjector->get(Config::class)->get('views.views_path'),
//                compiledPath: $this->dependencyInjector->get(Config::class)->get('views.cache_path')
//            )
//        );

        $this->dependencyInjector->singleton(
            abstract: BladeTemplateEngine::class,
            concrete: fn() : BladeTemplateEngine => new BladeTemplateEngine(
                viewsPath: $this->dependencyInjector->get(Config::class)->get('views.views_path'),
                cachePath: $this->dependencyInjector->get(Config::class)->get('views.cache_path')
            )
        );
    }

    public function boot() : void
    {
        // Optional: Additional view-related logic
    }
}
