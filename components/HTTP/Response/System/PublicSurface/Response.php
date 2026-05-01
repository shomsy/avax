<?php

declare(strict_types=1);

namespace Avax\Components\HTTP\Response\System\PublicSurface;

use Avax\Components\HTTP\Response\System\Capabilities\ResponseData\ResponseData;
use GuzzleHttp\Psr7\Utils;
use Psr\Http\Message\StreamInterface;

final class Response implements ResponseInterface
{
    private ResponseData $data;

    public function __construct(int $statusCode = 200, array $headers = [], StreamInterface $body = null, string $reasonPhrase = '', string $protocolVersion = '1.1')
    {
        if ($body === null) {
            $body = Utils::streamFor('');
        }
        $this->data = new ResponseData(
            statusCode     : $statusCode,
            headers        : $headers,
            body           : $body,
            reasonPhrase   : $reasonPhrase,
            protocolVersion: $protocolVersion,
        );
    }

    public function getProtocolVersion() : string
    {
        return $this->data->protocolVersion;
    }

    public function withProtocolVersion($version) : self
    {
        $clone = clone $this;
        $clone->data = $this->data->withProtocolVersion($version);

        return $clone;
    }

    public function getHeaders() : array
    {
        return $this->data->headers;
    }

    public function hasHeader($name) : bool
    {
        return isset($this->data->headers[strtolower($name)]);
    }

    public function getHeader($name) : array
    {
        return $this->data->headers[strtolower($name)] ?? [];
    }

    public function getHeaderLine($name) : string
    {
        return implode(', ', $this->getHeader($name));
    }

    public function withHeader($name, $value) : self
    {
        $clone = clone $this;
        $clone->data = $this->data->withHeader($name, is_array($value) ? $value : [$value]);

        return $clone;
    }

    public function withAddedHeader($name, $value) : self
    {
        return $this->withHeader($name, $value);
    }

    public function withoutHeader($name) : self
    {
        $clone = clone $this;
        $clone->data = $this->data->withoutHeader($name);

        return $clone;
    }

    public function getBody() : StreamInterface
    {
        return $this->data->body;
    }

    public function withBody(StreamInterface $body) : self
    {
        $clone = clone $this;
        $clone->data = $this->data->withBody($body);

        return $clone;
    }

    public function getStatusCode() : int
    {
        return $this->data->statusCode;
    }

    public function withStatus($code, $reasonPhrase = '') : self
    {
        $clone = clone $this;
        $clone->data = $this->data->withStatus($code, $reasonPhrase);

        return $clone;
    }

    public function getReasonPhrase() : string
    {
        return $this->data->reasonPhrase;
    }
}
