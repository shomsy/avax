<?php

declare(strict_types=1);

namespace Avax\Components\HTTP\Response\System\Capabilities\CreateHttpResponse;

use Avax\Components\HTTP\Response\System\Capabilities\ContentType;
use Avax\Components\HTTP\Response\System\PublicSurface\Response;
use GuzzleHttp\Psr7\Utils;
use JsonSerializable;
use Psr\Http\Message\ResponseInterface;

/**
 * CreateHttpResponse — Internal capability that creates Response objects.
 *
 * This is the single owner of response creation logic within the Response component.
 * It creates new Response() instances because Response is the produced result, not a service.
 *
 * Runtime flows receive this capability through DI.
 * PublicSurface (Responses) delegates to this capability.
 * ResponseServiceProvider registers this capability.
 */
final readonly class CreateHttpResponse
{
    /**
     * Create a generic response.
     */
    public function create(int $status = 200, array $headers = [], string $body = ''): Response
    {
        return new Response(
            statusCode: $status,
            headers   : $headers,
            stream    : Utils::streamFor($body),
        );
    }

    /**
     * Create a JSON response.
     */
    public function json(array|object $data, int $status = 200, array $headers = []): Response
    {
        $body = json_encode($data, JSON_THROW_ON_ERROR | JSON_UNESCAPED_UNICODE);
        $headers = $this->mergeHeaders($headers, ['content-type' => [ContentType::Json->value]]);

        return new Response(
            statusCode: $status,
            headers   : $headers,
            stream    : Utils::streamFor($body),
        );
    }

    /**
     * Create an HTML response.
     */
    public function html(string $content, int $status = 200, array $headers = []): Response
    {
        $headers = $this->mergeHeaders($headers, ['content-type' => [ContentType::Html->withCharset()]]);

        return new Response(
            statusCode: $status,
            headers   : $headers,
            stream    : Utils::streamFor($content),
        );
    }

    /**
     * Create a plain text response.
     */
    public function text(string $content, int $status = 200, array $headers = []): Response
    {
        $headers = $this->mergeHeaders($headers, ['content-type' => [ContentType::Plain->withCharset()]]);

        return new Response(
            statusCode: $status,
            headers   : $headers,
            stream    : Utils::streamFor($content),
        );
    }

    /**
     * Create a redirect response.
     */
    public function redirect(string $url, int $status = 302, array $headers = []): Response
    {
        $headers = $this->mergeHeaders($headers, ['location' => [$url]]);

        return new Response(
            statusCode: $status,
            headers   : $headers,
            stream    : Utils::streamFor('Redirecting to '.$url),
        );
    }

    /**
     * Create an empty (no body) response.
     */
    public function empty(int $status = 200, array $headers = []): Response
    {
        return new Response(
            statusCode: $status,
            headers   : $headers,
        );
    }

    /**
     * Create a no-content response (204).
     */
    public function noContent(array $headers = []): Response
    {
        return new Response(
            statusCode: 204,
            headers   : $headers,
        );
    }

    /**
     * Create an error response (JSON with error message).
     */
    public function error(string $message, int $status = 500, array $headers = []): Response
    {
        return $this->json(
            data   : ['message' => $message, 'error' => true],
            status : $status,
            headers: $headers,
        );
    }

    /**
     * Create a not-found error response.
     */
    public function notFound(string $message = 'Not Found', array $headers = []): Response
    {
        return $this->json(
            data   : ['error' => $message],
            status : 404,
            headers: $headers,
        );
    }

    /**
     * Create a method-not-allowed error response.
     *
     * @param list<string> $allowedMethods
     */
    public function methodNotAllowed(array $allowedMethods, array $headers = []): Response
    {
        return $this->json(
            data   : ['error' => 'Method Not Allowed', 'allowed' => $allowedMethods],
            status : 405,
            headers: $this->mergeHeaders($headers, ['allow' => [implode(', ', $allowedMethods)]]),
        );
    }

    /**
     * Create a rate-limited response (429).
     */
    public function rateLimited(int $retryAfter = 60, array $headers = []): Response
    {
        return $this->json(
            data   : ['message' => 'Too Many Requests', 'retry_after' => $retryAfter],
            status : 429,
            headers: $this->mergeHeaders($headers, ['retry-after' => [(string) $retryAfter]]),
        );
    }

    /**
     * Intelligent dispatch: creates appropriate response based on data type.
     * Arrays/JsonSerializable/objects → JSON. Strings → text. ResponseInterface → passthrough.
     */
    public function send(mixed $data, int $status = 200, array $headers = []): ResponseInterface
    {
        return match (true) {
            $data instanceof ResponseInterface                        => $data,
            $data instanceof JsonSerializable                         => $this->json(data: $data, status: $status, headers: $headers),
            is_array(value: $data)                                    => $this->json(data: $data, status: $status, headers: $headers),
            is_object(value: $data)                                   => $this->json(data: (array) $data, status: $status, headers: $headers),
            is_string(value: $data)                                   => $this->text(content: $data, status: $status, headers: $headers),
            default                                                   => $this->create(status: $status, headers: $headers),
        };
    }

    /**
     * Merge user headers with default headers. User headers take precedence.
     *
     * @param array<string, mixed> $userHeaders
     * @param array<string, mixed> $defaultHeaders
     * @return array<string, mixed>
     */
    private function mergeHeaders(array $userHeaders, array $defaultHeaders): array
    {
        return array_change_key_case($userHeaders) + $defaultHeaders;
    }
}
