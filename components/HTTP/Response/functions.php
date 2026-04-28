<?php

declare(strict_types=1);

use Avax\Components\HTTP\Response\Response;
use Psr\Http\Message\ResponseInterface;

if (! function_exists(function: 'response')) {
    /**
     * Create a new Response instance.
     *
     * @param string|null $content
     * @param int         $status
     * @param array       $headers
     *
     * @return ResponseInterface
     */
    function response(string|null $content = null, int $status = 200, #[SensitiveParameter] array $headers = []) : ResponseInterface
    {
        return Response::text(content: $content ?? '', status: $status, headers: $headers);
    }
}

if (! function_exists(function: 'redirect')) {
    /**
     * Create a new RedirectResponse instance.
     *
     * @param string $url
     * @param int    $status
     * @param array  $headers
     *
     * @return ResponseInterface
     */
    function redirect(string $url, int $status = 302, #[SensitiveParameter] array $headers = []) : ResponseInterface
    {
        return Response::redirect(url: $url, status: $status, headers: $headers);
    }
}
