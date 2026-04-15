<?php

declare(strict_types=1);

namespace Avax\HTTP\Request;

use Avax\HTTP\Request\IncomingHttp\IncomingRequest\PublicEntryPointRequest;
use Avax\HTTP\Request\IncomingHttp\IncomingRequest\ServerRequest;
use Psr\Http\Message\ServerRequestInterface;
use Psr\Http\Message\StreamInterface;
use Psr\Http\Message\UriInterface;

// TODO: rename ServerRequest class to ServerRequest

/**
 * Legacy Bridge: This class maintains the old namespace but delegates
 * everything to the new ServerRequest architecture.
 *
 * @deprecated Use Avax\HTTP\ServerRequest\ServerRequest\IncomingRequest\PublicEntryPointRequest instead.
 */
class Request implements ServerRequestInterface
{


    private function __construct(private readonly ServerRequest $serverRequest)
    {
    }

    public static function capture(): self
    {
        return new self(serverRequest: PublicEntryPointRequest::fromIncomingHttp());
    }

    /** PSR-7 Implementation via Delegation **/

    public function getProtocolVersion(): string
    {
        return $this->serverRequest->getProtocolVersion();
    }

    public function withProtocolVersion($version): static
    {
        $clone = clone $this;
        $clone->serverRequest = $this->serverRequest->withProtocolVersion(version: $version);

        return $clone;
    }

    public function getHeaders(): array
    {
        return $this->serverRequest->getHeaders();
    }

    public function hasHeader($name): bool
    {
        return $this->serverRequest->hasHeader(name: $name);
    }

    public function getHeader($name): array
    {
        return $this->serverRequest->getHeader(name: $name);
    }

    public function getHeaderLine($name): string
    {
        return $this->serverRequest->getHeaderLine(name: $name);
    }

    public function withHeader($name, $value): static
    {
        $clone = clone $this;
        $clone->serverRequest = $this->serverRequest->withHeader(name: $name, value: $value);

        return $clone;
    }

    public function withAddedHeader($name, $value): static
    {
        $clone = clone $this;
        $clone->serverRequest = $this->serverRequest->withAddedHeader(name: $name, value: $value);

        return $clone;
    }

    public function withoutHeader($name): static
    {
        $clone = clone $this;
        $clone->serverRequest = $this->serverRequest->withoutHeader(name: $name);

        return $clone;
    }

    public function getBody(): StreamInterface
    {
        return $this->serverRequest->getBody();
    }

    public function withBody(StreamInterface $body): static
    {
        $clone = clone $this;
        $clone->serverRequest = $this->serverRequest->withBody(body: $body);

        return $clone;
    }

    public function getRequestTarget(): string
    {
        return $this->serverRequest->getRequestTarget();
    }

    public function withRequestTarget($requestTarget): static
    {
        $clone = clone $this;
        $clone->serverRequest = $this->serverRequest->withRequestTarget(requestTarget: $requestTarget);

        return $clone;
    }

    public function getMethod(): string
    {
        return $this->serverRequest->getMethod();
    }

    public function withMethod($method): static
    {
        $clone = clone $this;
        $clone->serverRequest = $this->serverRequest->withMethod(method: $method);

        return $clone;
    }

    public function getUri(): UriInterface
    {
        return $this->serverRequest->getUri();
    }

    public function withUri(UriInterface $uri, $preserveHost = false): static
    {
        $clone = clone $this;
        $clone->serverRequest = $this->serverRequest->withUri(uri: $uri, preserveHost: $preserveHost);

        return $clone;
    }

    public function getServerParams(): array
    {
        return $this->serverRequest->getServerParams();
    }

    public function getCookieParams(): array
    {
        return $this->serverRequest->getCookieParams();
    }

    public function withCookieParams(array $cookies): static
    {
        $clone = clone $this;
        $clone->serverRequest = $this->serverRequest->withCookieParams(cookies: $cookies);

        return $clone;
    }

    public function getQueryParams(): array
    {
        return $this->serverRequest->getQueryParams();
    }

    public function withQueryParams(array $query): static
    {
        $clone = clone $this;
        $clone->serverRequest = $this->serverRequest->withQueryParams(query: $query);

        return $clone;
    }

    public function getUploadedFiles(): array
    {
        return $this->serverRequest->getUploadedFiles();
    }

    public function withUploadedFiles(array $uploadedFiles): static
    {
        $clone = clone $this;
        $clone->serverRequest = $this->serverRequest->withUploadedFiles(uploadedFiles: $uploadedFiles);

        return $clone;
    }

    public function getParsedBody(): array|object|null
    {
        return $this->serverRequest->getParsedBody();
    }

    public function withParsedBody($data): static
    {
        $clone = clone $this;
        $clone->serverRequest = $this->serverRequest->withParsedBody(data: $data);

        return $clone;
    }

    public function getAttributes(): array
    {
        return $this->serverRequest->getAttributes();
    }

    public function getAttribute($name, $default = null): mixed
    {
        return $this->serverRequest->getAttribute(name: $name, default: $default);
    }

    public function withAttribute($name, $value): static
    {
        $clone = clone $this;
        $clone->serverRequest = $this->serverRequest->withAttribute(name: $name, value: $value);

        return $clone;
    }

    public function withoutAttribute($name): static
    {
        $clone = clone $this;
        $clone->serverRequest = $this->serverRequest->withoutAttribute(name: $name);

        return $clone;
    }

    /** Legacy Helpers (Map to DSL) **/
    public function input(string $key, mixed $default = null): mixed
    {
        return $this->serverRequest->inputs()->get(key: $key, default: $default);
    }

    public function all(): array
    {
        return $this->serverRequest->inputs()->all();
    }
}
