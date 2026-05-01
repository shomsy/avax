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
    private RequestData $requestData;

    public function __construct(
        string $method,
        RequestUri     $requestUri,
        RequestHeaders $requestHeaders,
        RequestBody    $requestBody,
        UploadedFiles  $uploadedFiles,
        array  $serverParams = [],
        array  $cookieParams = [],
        array  $queryParams = [],
        array  $attributes = [],
        string $protocolVersion = '1.1',
    )
    {
        $this->requestData = new RequestData(
            method         : $method,
            uri            : $requestUri,
            headers        : $requestHeaders,
            body           : $requestBody,
            files          : $uploadedFiles,
            serverParams   : $serverParams,
            cookieParams   : $cookieParams,
            queryParams    : $queryParams,
            attributes     : $attributes,
            protocolVersion: $protocolVersion,
        );
    }

    public function getProtocolVersion() : string
    {
        return $this->requestData->protocolVersion;
    }

    public function withProtocolVersion($version) : self
    {
        $clone = clone $this;
        $clone->requestData = $this->requestData->withProtocolVersion($version);

        return $clone;
    }

    public function getHeaders() : array
    {
        return $this->requestData->headers->all();
    }

    public function hasHeader($name) : bool
    {
        return $this->requestData->headers->has($name);
    }

    public function getHeader($name) : array
    {
        return $this->requestData->headers->get($name)?->all() ?? [];
    }

    public function getHeaderLine($name) : string
    {
        return $this->requestData->headers->get($name)?->line() ?? '';
    }

    public function withHeader($name, $value) : self
    {
        $clone = clone $this;
        $clone->requestData->headers->set($name, $value);

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
        return Utils::streamFor($this->requestData->body->raw()->toString());
    }

    public function withBody(StreamInterface $body) : self
    {
        return $this;
    }

    public function getRequestTarget() : string
    {
        return $this->requestData->uri->getPath();
    }

    public function withRequestTarget($target) : self
    {
        return $this;
    }

    public function getMethod() : string
    {
        return $this->requestData->method;
    }

    public function withMethod($method) : self
    {
        $clone = clone $this;
        $clone->requestData = $this->requestData->withMethod($method);

        return $clone;
    }

    public function getUri() : UriInterface
    {
        return $this->requestData->uri;
    }

    public function withUri(UriInterface $uri, $preserveHost = false) : self
    {
        $clone = clone $this;
        $clone->requestData = $this->requestData->withUri($uri);

        return $clone;
    }

    public function getServerParams() : array
    {
        return $this->requestData->serverParams;
    }

    public function getCookieParams() : array
    {
        return $this->requestData->cookieParams;
    }

    public function withCookieParams(array $cookies) : self
    {
        $clone = clone $this;
        $clone->requestData = $this->requestData->withCookieParams($cookies);

        return $clone;
    }

    public function getQueryParams() : array
    {
        return $this->requestData->queryParams;
    }

    public function withQueryParams(array $query) : self
    {
        $clone = clone $this;
        $clone->requestData = $this->requestData->withQueryParams($query);

        return $clone;
    }

    public function getUploadedFiles() : array
    {
        return $this->requestData->files->all();
    }

    public function withUploadedFiles(array $uploadedFiles) : self
    {
        return $this;
    }

    public function getParsedBody() : array|object|null
    {
        return $this->requestData->body->parsed()->data();
    }

    public function withParsedBody($data) : self
    {
        return $this;
    }

    public function getAttributes() : array
    {
        return $this->requestData->attributes;
    }

    public function getAttribute($name, $default = null)
    {
        return $this->requestData->attributes[$name] ?? $default;
    }

    public function withAttribute($name, $value) : self
    {
        $clone = clone $this;
        $clone->requestData = $this->requestData->withAttribute($name, $value);

        return $clone;
    }

    public function withoutAttribute($name) : self
    {
        $clone = clone $this;
        $clone->requestData = $this->requestData->withoutAttribute($name);

        return $clone;
    }

    public function input(string $key, mixed $default = null) : mixed
    {
        return $this->requestData->attributes[$key] ?? $this->requestData->queryParams[$key] ?? ($this->getParsedBody()[$key] ?? $default);
    }

    public function all() : array
    {
        return array_merge($this->requestData->queryParams, (array) $this->getParsedBody(), $this->requestData->attributes);
    }
}
