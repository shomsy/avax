<?php

declare(strict_types=1);

namespace Avax\Components\HTTP\Request\System\PublicSurface;

use Avax\Components\HTTP\Request\System\Capabilities\Body\RequestBody;
use Avax\Components\HTTP\Request\System\Capabilities\Files\UploadedFiles;
use Avax\Components\HTTP\Request\System\Capabilities\Headers\RequestHeaders;
use Avax\Components\HTTP\Request\System\Capabilities\RequestData\RequestData;
use Avax\Components\HTTP\Request\System\Capabilities\Uri\RequestUri;
use GuzzleHttp\Psr7\Utils;
use Psr\Http\Message\StreamInterface;
use Psr\Http\Message\UriInterface;

final class Request implements RequestInterface
{
    private RequestData $data;

    public function __construct(
        string $method,
        RequestUri $uri,
        RequestHeaders $headers,
        RequestBody $body,
        UploadedFiles $files,
        array  $serverParams = [],
        array  $cookieParams = [],
        array  $queryParams = [],
        array  $attributes = [],
        string $protocolVersion = '1.1',
    )
    {
        $this->data = new RequestData(
            method         : $method,
            uri            : $uri,
            headers        : $headers,
            body           : $body,
            files          : $files,
            serverParams   : $serverParams,
            cookieParams   : $cookieParams,
            queryParams    : $queryParams,
            attributes     : $attributes,
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
        return $this->data->headers->all();
    }

    public function hasHeader($name) : bool
    {
        return $this->data->headers->has($name);
    }

    public function getHeader($name) : array
    {
        return $this->data->headers->get($name)?->all() ?? [];
    }

    public function getHeaderLine($name) : string
    {
        return $this->data->headers->get($name)?->line() ?? '';
    }

    public function withHeader($name, $value) : self
    {
        $clone = clone $this;
        $clone->data->headers->set($name, $value);

        return $clone;
    }

    public function withAddedHeader($name, $value) : self
    {
        return $this->withHeader($name, $value);
    }

    public function withoutHeader($name) : self
    {
        return $this;
    }

    public function getBody() : StreamInterface
    {
        return Utils::streamFor($this->data->body->raw()->toString());
    }

    public function withBody(StreamInterface $body) : self
    {
        return $this;
    }

    public function getRequestTarget() : string
    {
        return $this->data->uri->getPath();
    }

    public function withRequestTarget($target) : self
    {
        return $this;
    }

    public function getMethod() : string
    {
        return $this->data->method;
    }

    public function withMethod($method) : self
    {
        $clone = clone $this;
        $clone->data = $this->data->withMethod($method);

        return $clone;
    }

    public function getUri() : UriInterface
    {
        return $this->data->uri;
    }

    public function withUri(UriInterface $uri, $preserveHost = false) : self
    {
        $clone = clone $this;
        $clone->data = $this->data->withUri($uri);

        return $clone;
    }

    public function getServerParams() : array
    {
        return $this->data->serverParams;
    }

    public function getCookieParams() : array
    {
        return $this->data->cookieParams;
    }

    public function withCookieParams(array $cookies) : self
    {
        $clone = clone $this;
        $clone->data = $this->data->withCookieParams($cookies);

        return $clone;
    }

    public function getQueryParams() : array
    {
        return $this->data->queryParams;
    }

    public function withQueryParams(array $query) : self
    {
        $clone = clone $this;
        $clone->data = $this->data->withQueryParams($query);

        return $clone;
    }

    public function getUploadedFiles() : array
    {
        return $this->data->files->all();
    }

    public function withUploadedFiles(array $uploadedFiles) : self
    {
        return $this;
    }

    public function getParsedBody() : array|object|null
    {
        return $this->data->body->parsed()->data();
    }

    public function withParsedBody($data) : self
    {
        return $this;
    }

    public function getAttributes() : array
    {
        return $this->data->attributes;
    }

    public function getAttribute($name, $default = null)
    {
        return $this->data->attributes[$name] ?? $default;
    }

    public function withAttribute($name, $value) : self
    {
        $clone = clone $this;
        $clone->data = $this->data->withAttribute($name, $value);

        return $clone;
    }

    public function withoutAttribute($name) : self
    {
        $clone = clone $this;
        $clone->data = $this->data->withoutAttribute($name);

        return $clone;
    }

    public function input(string $key, mixed $default = null) : mixed
    {
        return $this->data->attributes[$key] ?? $this->data->queryParams[$key] ?? ($this->getParsedBody()[$key] ?? $default);
    }

    public function all() : array
    {
        return array_merge($this->data->queryParams, (array) $this->getParsedBody(), $this->data->attributes);
    }
}
