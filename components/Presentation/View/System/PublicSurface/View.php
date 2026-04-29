<?php

declare(strict_types=1);

namespace Avax\Components\Presentation\View\System\PublicSurface;

use Avax\Components\Presentation\View\System\Capabilities\Engines\BladeTemplateEngine;
use Avax\Components\Presentation\View\System\Capabilities\TemplateEngineInterface;

/**
 * Public surface for the View component.
 */
final class View implements ViewInterface
{
    private static ?TemplateEngineInterface $engine = null;
    private static array $config = [];

    public static function setEngine(TemplateEngineInterface $engine): void
    {
        self::$engine = $engine;
    }

    public static function configure(array $config): void
    {
        self::$config = $config;
    }

    public static function config(string $key, mixed $default = null): mixed
    {
        return self::$config[$key] ?? $default;
    }

    public static function render(string $template, array $data = []): string
    {
        return self::getEngine()->renderView($template, $data);
    }

    public static function exists(string $view): bool
    {
        return self::getEngine()->exists($view);
    }

    public static function share(string $key, mixed $value): void
    {
        self::getEngine()->share($key, $value);
    }

    private static function getEngine(): TemplateEngineInterface
    {
        if (self::$engine === null) {
            self::$engine = new BladeTemplateEngine(
                self::$config['path'] ?? base_path('resources/views'),
                self::$config['cache_path'] ?? base_path('storage/views'),
            );
        }

        return self::$engine;
    }
}