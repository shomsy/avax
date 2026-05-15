<?php

declare(strict_types=1);

/**
 * View shortcuts for global access.
 */

use Avax\Components\HTTP\Response\System\PublicSurface\Responses;
use Avax\Components\Presentation\View\System\Capabilities\TemplateRendering\TemplateEngine;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;

if (! function_exists('view')) {
    /**
     * Renders a Blade view and returns an HTTP response.
     *
     * @param  string  $template  The view template to render.
     * @param  array  $data  The data to pass to the view.
     */
    function view(string $template, array $data = []): ResponseInterface
    {
        /** @var TemplateEngine $engine */
        $engine = app(TemplateEngine::class);

        return app(Responses::class)->html(content: $engine->render($template, $data));
    }
}
