<?php

declare(strict_types=1);

namespace Avax\Components\HTTP\Response\System\PublicSurface;

use Avax\Components\HTTP\Response\System\Capabilities\CreateHttpResponse\CreateHttpResponse;
use Psr\Http\Message\ResponseFactoryInterface;
use Psr\Http\Message\ResponseInterface;
use SensitiveParameter;

/**
 * Responses — PublicSurface/fluent API for HTTP response creation.
 *
 * This is the developer-friendly entry point for creating responses.
 * It delegates all construction logic to CreateHttpResponse.
 * It does not encode JSON, create Response objects, or access the container.
 */
final readonly class Responses implements ResponseFactoryInterface
{
    public function __construct(
        private CreateHttpResponse $createHttpResponse,
    ) {
    }

    /**
     * Intelligent dispatch: creates appropriate response based on data type.
     */
    public function send(mixed $data, int $status = 200): ResponseInterface
    {
        return $this->createHttpResponse->send(data: $data, status: $status);
    }

    /**
     * Alias for send().
     */
    public function response(mixed $data, int $status = 200): ResponseInterface
    {
        return $this->send(data: $data, status: $status);
    }

    /**
     * Create a JSON response.
     */
    public function json(array|object $data, int $status = 200, array $headers = []): ResponseInterface
    {
        return $this->createHttpResponse->json(data: $data, status: $status, headers: $headers);
    }

    /**
     * Create an HTML response.
     */
    public function html(string $content, int $status = 200, array $headers = []): ResponseInterface
    {
        return $this->createHttpResponse->html(content: $content, status: $status, headers: $headers);
    }

    /**
     * Create a plain text response.
     */
    public function text(string $content, int $status = 200, array $headers = []): ResponseInterface
    {
        return $this->createHttpResponse->text(content: $content, status: $status, headers: $headers);
    }

    /**
     * Create a redirect response.
     */
    public function redirect(string $url, int $status = 302, array $headers = []): ResponseInterface
    {
        return $this->createHttpResponse->redirect(url: $url, status: $status, headers: $headers);
    }

    /**
     * Create an empty (no body) response.
     */
    public function empty(int $status = 200, array $headers = []): ResponseInterface
    {
        return $this->createHttpResponse->empty(status: $status, headers: $headers);
    }

    /**
     * Create a no-content response (204).
     */
    public function noContent(array $headers = []): ResponseInterface
    {
        return $this->createHttpResponse->noContent(headers: $headers);
    }

    /**
     * Create an error response.
     */
    public function error(string $message, int $status = 500, array $headers = []): ResponseInterface
    {
        return $this->createHttpResponse->error(message: $message, status: $status, headers: $headers);
    }

    /**
     * PSR-17: create a generic response.
     */
    public function createResponse(int $code = 200, string $reasonPhrase = ''): ResponseInterface
    {
        return $this->createHttpResponse->create(status: $code);
    }

    /**
     * PSR-17 compatibility: create a response with body.
     */
    public function createResponseWithBody(string $content, int $status, #[SensitiveParameter] array $headers = []): ResponseInterface
    {
        return $this->createHttpResponse->create(status: $status, headers: $headers, body: $content);
    }
}
