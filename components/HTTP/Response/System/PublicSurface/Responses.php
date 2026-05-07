<?php

declare(strict_types=1);

namespace Avax\Components\HTTP\Response\System\PublicSurface;

use Avax\Components\HTTP\Response\System\Flows\BuildResponse\BuildEmptyResponse;
use Avax\Components\HTTP\Response\System\Flows\BuildResponse\BuildHtmlResponse;
use Avax\Components\HTTP\Response\System\Flows\BuildResponse\BuildJsonResponse;
use Avax\Components\HTTP\Response\System\Flows\BuildResponse\BuildRedirectResponse;
use Avax\Components\HTTP\Response\System\Flows\BuildResponse\BuildTextResponse;
use JsonSerializable;
use Psr\Http\Message\ResponseFactoryInterface;
use Psr\Http\Message\ResponseInterface;
use SensitiveParameter;
use Stringable;

/**
 * Entry point for creating HTTP response instances.
 * Implements PSR-17 and provides compatibility helpers.
 */
final class Responses implements ResponseFactoryInterface
{
    public function response(mixed $data, int $status = 200) : ResponseInterface
    {
        return $this->send(data: $data, status: $status);
    }

    /**
     * Compatibility wrapper for the legacy mixed-dispatch API.
     */
    public function send(mixed $data, int $status = 200) : ResponseInterface
    {
        return match (true) {
            $data instanceof ResponseInterface                       => $data,
            is_array(value: $data)                                   => $this->createJsonResponse(data: $data, status: $status),
            $data instanceof JsonSerializable                        => Response::json(data: $data, status: $status),
            is_object(value: $data) && ! $data instanceof Stringable => Response::json(data: (array) $data, status: $status),
            is_string(value: $data), $data instanceof Stringable     => $this->createTextResponse(content: (string) $data, status: $status),
            default                                                  => $this->createResponseWithBody(content: (string) ($data ?? ''), status: $status),
        };
    }

    public function createJsonResponse(array $data, int $status = 200) : ResponseInterface
    {
        return BuildJsonResponse::execute(data: $data, status: $status);
    }

    public function createTextResponse(string $content, int $status = 200) : ResponseInterface
    {
        return BuildTextResponse::execute(content: $content, status: $status);
    }

    public function createResponseWithBody(string $content, int $status, #[SensitiveParameter] array $headers = []) : ResponseInterface
    {
        return new Response(
            statusCode: $status,
            headers   : $headers,
        );
    }

    public function createErrorResponse(int $statusCode, string $message) : ResponseInterface
    {
        return $this->createJsonResponse(data: ['error' => $message], status: $statusCode);
    }

    public function createResponse(int $code = 200, string $reasonPhrase = '') : ResponseInterface
    {
        return BuildEmptyResponse::execute(
            status      : $code,
            reasonPhrase: $reasonPhrase,
        );
    }

    public function createRedirectResponse(string $url, int $status = 302) : ResponseInterface
    {
        return BuildRedirectResponse::execute(target: $url, status: $status);
    }

    public function createHtmlResponse(string $html, int $status = 200) : ResponseInterface
    {
        return BuildHtmlResponse::execute(content: $html, status: $status);
    }
}
