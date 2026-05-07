<?php

declare(strict_types=1);

namespace Avax\Components\HTTP\Response;

use Avax\Components\HTTP\Response\System\System\PublicSurface\Response;
use Avax\Components\HTTP\Response\System\System\PublicSurface\ResponseInterface;
use GuzzleHttp\Psr7\Utils;

/**
 * Factory for creating HTTP response instances.
 */
final class ResponseFactory
{
    /**
     * Create a new response.
     */
    public function create(int $statusCode = 200, array $headers = [], string $body = ''): ResponseInterface
    {
        return new Response($statusCode, $headers, Utils::streamFor($body));
    }

    /**
     * Create an HTML response.
     */
    public function html(string $html, int $statusCode = 200, array $headers = []): ResponseInterface
    {
        $headers = array_change_key_case($headers) + ['content-type' => ['text/html; charset=utf-8']];

        return new Response($statusCode, $headers, Utils::streamFor($html));
    }

    /**
     * Create a redirect response.
     */
    public function redirect(string $url, int $statusCode = 302): ResponseInterface
    {
        return new Response($statusCode, ['location' => [$url]], Utils::streamFor('Redirecting to '.$url));
    }

    /**
     * Create a rate limited response (429).
     */
    public function rateLimited(int $retryAfter = 60): ResponseInterface
    {
        return $this->json(
            ['message' => 'Too Many Requests', 'retry_after' => $retryAfter],
            429,
            ['retry-after' => [(string) $retryAfter]],
        );
    }

    /**
     * Create a JSON response.
     */
    public function json(mixed $data, int $statusCode = 200, array $headers = []): ResponseInterface
    {
        $body = json_encode($data, JSON_THROW_ON_ERROR);
        $headers = array_change_key_case($headers) + ['content-type' => ['application/json']];

        return new Response($statusCode, $headers, Utils::streamFor($body));
    }

    /**
     * Create a not found response.
     */
    public function notFound(string $message = 'Not Found'): ResponseInterface
    {
        return $this->json(['message' => $message], 404);
    }

    /**
     * Create an error response.
     */
    public function error(string $message, int $statusCode = 500): ResponseInterface
    {
        return $this->json(['message' => $message, 'error' => true], $statusCode);
    }

    /**
     * Create an error response (alias for error).
     */
    public function createErrorResponse(string $message, int $statusCode = 500): ResponseInterface
    {
        return $this->error($message, $statusCode);
    }

    /**
     * Create a no content response.
     */
    public function noContent(): ResponseInterface
    {
        return new Response(204);
    }

    /**
     * Create an empty response.
     */
    public function empty(int $statusCode = 200): ResponseInterface
    {
        return new Response($statusCode);
    }
}
