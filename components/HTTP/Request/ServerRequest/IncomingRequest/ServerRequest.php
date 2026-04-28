<?php

declare(strict_types=1);

namespace Avax\Components\HTTP\Request\ServerRequest\IncomingRequest;

use Avax\Components\HTTP\Request\ServerRequest\IncomingRequest\RequestedInputs\RequestedInputs;
use Psr\Http\Message\ServerRequestInterface;
use Psr\Http\Message\StreamInterface;
use Psr\Http\Message\UriInterface;

/**
 * ServerRequest - The thin, immutable PSR-7 surface.
 */
final class ServerRequest implements ServerRequestInterface
{
    public string $protocolVersion {
        get => $this->setup->state->protocolVersion;
    }
    public array $headers {
        get => $this->setup->state->requestHeaders->all();
    }
    public string|null $requestTarget {
        get => $this->setup->state->requestTarget;
    }
    public string $method {
        get => $this->setup->state->method;
    }
    public UriInterface|null $uri {
        get => $this->setup->state->uri;
    }
    public array $serverParams {
        get => $this->setup->state->serverParams;
    }
    public array $queryParams {
        get => $this->setup->state->queryParams;
    }

    public function __construct(private readonly ServerInit $setup) {}


    // PSR-7 Interface Methods

    public function getProtocolVersion() : string
    {
        return $this->setup->state->protocolVersion;
    }

    public function withProtocolVersion($version) : self
    {
        return $this->initializeRequest(
            state: $this->setup->state->withProtocolVersion(protocolVersion: $version)
        );
    }

    private function initializeRequest(RequestInit $state) : self
    {
        return clone(object: $this, withProperties: [
            "setup" => $this->setup->withState(state: $state)
        ]);
    }

    public function getHeaders() : array
    {
        return $this->setup->state->requestHeaders->all();
    }

    public function getHeader($name) : array
    {
        return $this->setup->state->requestHeaders->get(name: $name);
    }

    public function getHeaderLine($name) : string
    {
        return $this->setup->state->requestHeaders->getLine(name: $name);
    }

    public function withHeader($name, $value) : self
    {
        return $this->initializeRequest(
            state: $this->setup->state->withRequestHeaders(
                     requestHeaders: $this->setup->state->requestHeaders->put(name: $name, value: $value)
                 )
        );
    }

    public function withAddedHeader($name, $value) : self
    {
        return $this->initializeRequest(
            state: $this->setup->state->withRequestHeaders(
                     requestHeaders: $this->setup->state->requestHeaders->append(name: $name, value: $value)
                 )
        );
    }

    public function withoutHeader($name) : self
    {
        return $this->initializeRequest(
            state: $this->setup->state->withRequestHeaders(
                     requestHeaders: $this->setup->state->requestHeaders->drop(name: $name)
                 )
        );
    }

    public function getBody() : StreamInterface
    {
        return $this->setup->state->body->stream();
    }

    public function withBody(StreamInterface $body) : self
    {
        return $this->initializeRequest(
            state: $this->setup->state->withBody(
                     body: new RequestBody\RequestBody(stream: $body)
                 )
        );
    }

    public function getRequestTarget() : string
    {
        return $this->setup->state->requestTarget;
    }

    public function withRequestTarget($requestTarget) : self
    {
        return $this->initializeRequest(
            state: $this->setup->state->withRequestTarget(requestTarget: $requestTarget)
        );
    }

    public function getMethod() : string
    {
        return $this->setup->state->method;
    }

    public function withMethod($method) : self
    {
        return $this->initializeRequest(
            state: $this->setup->state->withMethod(method: $method)
        );
    }

    public function getUri() : UriInterface
    {
        return $this->setup->state->uri;
    }

    public function withUri(UriInterface $uri, $preserveHost = false) : self
    {
        $state = $this->setup->state->withUri(uri: $uri);

        if ($preserveHost && $this->hasHeader(name: 'Host')) {
            return $this->initializeRequest(state: $state);
        }

        $host = $uri->getHost();
        if ($host === '') {
            return $this->initializeRequest(state: $state);
        }

        if (($port = $uri->getPort()) !== null) {
            $host .= ':' . $port;
        }

        return $this->initializeRequest(
            state: $state->withRequestHeaders(
                     requestHeaders: $state->requestHeaders->put(name: 'Host', value: $host)
                 )
        );
    }

    public function hasHeader($name) : bool
    {
        return $this->setup->state->requestHeaders->has(name: $name);
    }

    public function getServerParams() : array
    {
        return $this->setup->state->serverParams;
    }

    public function getCookieParams() : array
    {
        return $this->setup->state->cookies->all();
    }

    public function withCookieParams(array $cookies) : self
    {
        return $this->initializeRequest(
            state: $this->setup->state->withCookies(
                     cookies: new RequestCookies\RequestCookies(cookies: $cookies)
                 )
        );
    }

    public function getQueryParams() : array
    {
        return $this->setup->state->queryParams;
    }

    public function withQueryParams(array $query) : self
    {
        return $this->initializeRequest(
            state: $this->setup->state->withQueryParams(queryParams: $query)
        );
    }

    public function getUploadedFiles() : array
    {
        return $this->setup->state->uploadedFiles->all();
    }

    public function withUploadedFiles(array $uploadedFiles) : self
    {
        return $this->initializeRequest(
            state: $this->setup->state->withUploadedFiles(
                     uploadedFiles: new UploadedFiles\UploadedFiles(files: $uploadedFiles)
                 )
        );
    }

    public function getParsedBody() : array|object|null
    {
        return $this->setup->state->parsedBody->data();
    }

    public function withParsedBody($data) : self
    {
        return $this->initializeRequest(
            state: $this->setup->state->withParsedBody(
                     parsedBody: new RequestBody\ParsedBody(data: $data)
                 )
        );
    }

    public function getAttributes() : array
    {
        return $this->setup->state->attributes->all();
    }

    public function getAttribute($name, $default = null) : mixed
    {
        return $this->setup->state->attributes->get(name: $name, default: $default);
    }

    public function withAttribute($name, $value) : self
    {
        return $this->initializeRequest(
            state: $this->setup->state->withAttributes(
                     attributes: $this->setup->state->attributes->put(name: $name, value: $value)
                 )
        );
    }

    // Custom Capabilities

    public function withoutAttribute($name) : self
    {
        return $this->initializeRequest(
            state: $this->setup->state->withAttributes(
                     attributes: $this->setup->state->attributes->drop(name: $name)
                 )
        );
    }

    public function inputs() : RequestedInputs
    {
        return RequestedInputs::fromQueryAndBody(
            queryParams: $this->setup->state->queryParams,
            parsedBody : (array) ($this->setup->state->parsedBody->data() ?? []),
            sanitizer  : $this->setup->sanitizer,
            mapper     : $this->setup->mapper,
        );
    }

    public function resolveClientAddress(array $serverParams) : string|null
    {
        return $this->setup->preparer->resolveClientAddress(serverParams: $serverParams);
    }
}
