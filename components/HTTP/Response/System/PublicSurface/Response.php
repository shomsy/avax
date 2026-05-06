<?php

declare(strict_types=1);

namespace Avax\Components\HTTP\Response\System\PublicSurface;

use Avax\Components\HTTP\Response\System\Capabilities\ResponseData\ResponseData;
use GuzzleHttp\Psr7\Utils;
use Psr\Http\Message\StreamInterface;

final class Response implements ResponseInterface
{
    private ResponseData $responseData;

    public function __construct(int $statusCode = 200, array $headers = [], ?StreamInterface $stream = null, string $reasonPhrase = '', string $protocolVersion = '1.1')
    {
        if (! $stream instanceof StreamInterface) {
            $stream = Utils::streamFor('');
        }

        $this->responseData = new ResponseData(
            statusCode     : $statusCode,
            headers        : $headers,
            body           : $stream,
            reasonPhrase   : $reasonPhrase,
            protocolVersion: $protocolVersion,
        );
    }

    public function getProtocolVersion(): string
    {
        return $this->responseData->protocolVersion;
    }

    public function withProtocolVersion($version): self
    {
        $clone = clone $this;
        $clone->responseData = $this->responseData->withProtocolVersion($version);

        return $clone;
    }

    public function getHeaders(): array
    {
        return $this->responseData->headers;
    }

    public function hasHeader($name): bool
    {
        return isset($this->responseData->headers[strtolower($name)]);
    }

    public function getHeader($name): array
    {
        return $this->responseData->headers[strtolower($name)] ?? [];
    }

    public function getHeaderLine($name): string
    {
        return implode(', ', $this->getHeader($name));
    }

    public function withHeader($name, $value): self
    {
        $clone = clone $this;
        $clone->responseData = $this->responseData->withHeader($name, is_array($value) ? $value : [$value]);

        return $clone;
    }

    public function withAddedHeader($name, $value): self
    {
        return $this->withHeader($name, $value);
    }

    public function withoutHeader($name): self
    {
        $clone = clone $this;
        $clone->responseData = $this->responseData->withoutHeader($name);

        return $clone;
    }

    public function getBody(): StreamInterface
    {
        return $this->responseData->body;
    }

    public function withBody(StreamInterface $body): self
    {
        $clone = clone $this;
        $clone->responseData = $this->responseData->withBody($body);

        return $clone;
    }

    public function getStatusCode(): int
    {
        return $this->responseData->statusCode;
    }

    public function withStatus($code, $reasonPhrase = ''): self
    {
        $clone = clone $this;
        $clone->responseData = $this->responseData->withStatus($code, $reasonPhrase);

        return $clone;
    }

    public function getReasonPhrase(): string
    {
        return $this->responseData->reasonPhrase;
    }

    public static function text(string $content, int $status = 200): self
    {
        return new self($status, ['Content-Type' => ['text/plain; charset=utf-8']], Utils::streamFor($content));
    }

    public static function json(mixed $data, int $status = 200): self
    {
        return new self($status, ['Content-Type' => ['application/json']], Utils::streamFor(json_encode($data, JSON_THROW_ON_ERROR)));
    }

    public static function html(string $content, int $status = 200): self
    {
        return new self($status, ['Content-Type' => ['text/html; charset=utf-8']], Utils::streamFor($content));
    }

    public static function redirect(string $url, int $status = 302): self
    {
        return new self($status, ['Location' => [$url]], Utils::streamFor('Redirecting to '.$url));
    }
}
