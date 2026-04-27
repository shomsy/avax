<?php

declare(strict_types=1);

namespace Avax\Components\View\System\PublicSurface;

use Avax\Components\View\System\Capabilities\Engines\BladeTemplateEngine;

/**
 * Public surface for the View component.
 */
final class View implements ViewInterface
{
    private BladeTemplateEngine $engine;

    public function __construct(?string $viewsPath = null, ?string $cachePath = null)
    {
        $viewsPath ??= '/home/shomsy/projects/avax/resources/views';
        $cachePath ??= '/home/shomsy/projects/avax/storage/framework/views';

        $this->engine = new BladeTemplateEngine($viewsPath, $cachePath);
    }

    public function render(string $template, array $data = []): string
    {
        return $this->engine->renderView($template, $data);
    }

    public function exists(string $view) : bool
    {
        return $this->engine->exists($view);
    }

    public function share(string $key, mixed $value) : void
    {
        $this->engine->share($key, $value);
    }
}