<?php

declare(strict_types=1);

namespace Avax\HTTP\Response\Capabilities\Message;

use Avax\HTTP\Response\Capabilities\Body\ResponseBody;
use Avax\HTTP\Response\Capabilities\Headers\ResponseHeaders;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\StreamInterface;

/**
 * Immutable PSR-7 response state owner.
 */
final class ResponseMessage implements ResponseInterface
{
    private int $statusCode;

    private string $reasonPhrase;

    private string $protocolVersion;

    private ResponseHeaders $headers;

    private ResponseBody $body;

    public function __construct(
        int                        $statusCode = 200,
        string                     $reasonPhrase = '',
        string                     $protocolVersion = '1.1',
        array|ResponseHeaders|null $headers = null,
        ResponseBody|null          $body = null,
    )
    {
        $headers ??= new ResponseHeaders();
        $body    ??= new ResponseBody();

        $this->statusCode      = (new ValidateStatusCode())($statusCode);
        $this->reasonPhrase    = (new ResolveReasonPhrase())($this->statusCode, $reasonPhrase);
        $this->protocolVersion = (new NormalizeProtocolVersion())($protocolVersion);
        $this->headers         = $headers instanceof ResponseHeaders ? $headers : new ResponseHeaders($headers);
        $this->body            = $body;
    }

    public function getProtocolVersion() : string
    {
        return $this->protocolVersion;
    }

    public function withProtocolVersion(string $version) : ResponseInterface
    {
        $clone                  = clone $this;
        $clone->protocolVersion = (new NormalizeProtocolVersion())($version);

        return $clone;
    }

    public function getHeaders() : array
    {
        return $this->headers->toArray();
    }

    public function hasHeader(string $name) : bool
    {
        return $this->headers->has(name: $name);
    }

    public function getHeader(string $name) : array
    {
        return $this->headers->read(name: $name);
    }

    public function getHeaderLine(string $name) : string
    {
        return $this->headers->readLine(name: $name);
    }

    public function withHeader(string $name, $value) : ResponseInterface
    {
        $clone          = clone $this;
        $clone->headers = $this->headers->replace(name: $name, value: $value);

        return $clone;
    }

    public function withAddedHeader(string $name, $value) : ResponseInterface
    {
        $clone          = clone $this;
        $clone->headers = $this->headers->append(name: $name, value: $value);

        return $clone;
    }

    public function withoutHeader(string $name) : ResponseInterface
    {
        $clone          = clone $this;
        $clone->headers = $this->headers->remove(name: $name);

        return $clone;
    }

    public function getBody() : StreamInterface
    {
        return $this->body->stream();
    }

    public function withBody(StreamInterface $body) : ResponseInterface
    {
        $clone       = clone $this;
        $clone->body = new ResponseBody(stream: $body);

        return $clone;
    }

    public function getStatusCode() : int
    {
        return $this->statusCode;
    }

    public function withStatus(int $code, string $reasonPhrase = '') : ResponseInterface
    {
        $clone               = clone $this;
        $clone->statusCode   = (new ValidateStatusCode())($code);
        $clone->reasonPhrase = (new ResolveReasonPhrase())($clone->statusCode, $reasonPhrase);

        return $clone;
    }

    public function getReasonPhrase() : string
    {
        return $this->reasonPhrase;
    }
}
