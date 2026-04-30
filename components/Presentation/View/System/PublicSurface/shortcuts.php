<?php

declare(strict_types=1);

/**
 * View shortcuts for global access.
 */

use Avax\Components\HTTP\Response\Response;
use Avax\Components\Presentation\View\TemplateEngine;
use Psr\Http\Message\ResponseInterface;

if (! function_exists('view')) {
    /**
     * Renders a Blade view and returns an HTTP response.
     *
     * @param string $template The view template to render.
     * @param array  $data     The data to pass to the view.
     *
     * @return ResponseInterface
     */
    function view(string $template, array $data = []) : ResponseInterface
    {
        /** @var TemplateEngine $engine */
        $engine = app(TemplateEngine::class);

        return Response::html($engine->render($template, $data));
    }
}

if (! function_exists('asset')) {
    /**
     * Generate a URL for an asset.
     *
     * @param string $path
     *
     * @return string
     */
    function asset(string $path) : string
    {
        $baseUrl = (! empty($_SERVER['HTTPS']) && $_SERVER['HTTPS'] !== 'off') ? 'https://' : 'http://';
        $baseUrl .= $_SERVER['HTTP_HOST'] ?? 'localhost';

        return $baseUrl . '/' . ltrim($path, '/');
    }
}
