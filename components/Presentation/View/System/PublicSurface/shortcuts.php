<?php

declare(strict_types=1);

/**
 * View shortcuts for global access.
 */

use Avax\Components\HTTP\Response\System\PublicSurface\Response;
use Avax\Components\Presentation\View\TemplateEngine;
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

        return Response::html($engine->render($template, $data));
    }
}

if (! function_exists('asset')) {
    /**
     * Generate a URL for an asset.
     */
    function asset(string $path): string
    {
        $request = app(ServerRequestInterface::class);
        $uri     = $request->getUri();
        $baseUrl = $uri->getScheme() . '://' . $uri->getHost();
        $port    = $uri->getPort();
        if ($port !== null) {
            $baseUrl .= ':' . $port;
        }

        return $baseUrl.'/'.ltrim($path, '/');
    }
}
