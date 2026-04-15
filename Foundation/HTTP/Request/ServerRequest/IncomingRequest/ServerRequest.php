<?php

declare(strict_types=1);

namespace Avax\HTTP\Request\IncomingHttp\IncomingRequest;

use Avax\HTTP\Request\IncomingHttp\IncomingRequest\ProtocolVersion\NormalizeProtocolVersion;
use Avax\HTTP\Request\IncomingHttp\IncomingRequest\RequestAttributes\RequestAttributes;
use Avax\HTTP\Request\IncomingHttp\IncomingRequest\RequestBody\ParsedBody;
use Avax\HTTP\Request\IncomingHttp\IncomingRequest\RequestBody\RequestBody;
use Avax\HTTP\Request\IncomingHttp\IncomingRequest\RequestCookies\RequestCookies;
use Avax\HTTP\Request\IncomingHttp\IncomingRequest\RequestedInputs\RequestedInputs;
use Avax\HTTP\Request\IncomingHttp\IncomingRequest\RequestHeaders\RequestHeaders;
use Avax\HTTP\Request\IncomingHttp\IncomingRequest\RequestSession\RequestSession;
use Avax\HTTP\Request\IncomingHttp\IncomingRequest\RequestTarget\ReadRequestTarget;
use Avax\HTTP\Request\IncomingHttp\IncomingRequest\UploadedFiles\UploadedFiles;
use Avax\HTTP\URI\UriBuilder;
use Psr\Http\Message\ServerRequestInterface;
use Psr\Http\Message\StreamInterface;
use Psr\Http\Message\UriInterface;
use SensitiveParameter;

/**
 * State Owner: Clean, immutable PSR-7 Server ServerRequest.
 *
 * It delegates all semantic behavior to capability owners.
 */
final readonly class ServerRequest implements ServerRequestInterface
{
    private string $protocolVersion;
    private RequestSession|null $session;
    private RequestAttributes $attributes;
    private ParsedBody $parsedBody;
    private UploadedFiles $uploadedFiles;
    private array $queryParams;
    private RequestCookies $cookies;
    private string|null $requestTarget;
    private array $serverParams;
    private RequestBody $body;
    private RequestHeaders $headers;
    private UriBuilder $uri;
    private string $method;

    public function __construct(
        RequestBody $body,
        string|null $method = null,
        UriBuilder|null $uri = null,
        #[SensitiveParameter] RequestHeaders|null $headers = null,
        array|null $serverParams = null,
        string|null $requestTarget = null,
        RequestCookies|null $cookies = null,
        array|null $queryParams = null,
        UploadedFiles|null $uploadedFiles = null,
        ParsedBody|null $parsedBody = null,
        RequestAttributes|null $attributes = null,
        #[SensitiveParameter] RequestSession|null $session = null,
        string $protocolVersion = '1.1'
    ) {
        $method ??= 'GET';
        $uri ??= new UriBuilder(scheme: '');
        $headers ??= new RequestHeaders();
        $serverParams ??= [];
        $cookies ??= new RequestCookies();
        $queryParams ??= [];
        $uploadedFiles ??= new UploadedFiles();
        $parsedBody ??= new ParsedBody();
        $attributes ??= new RequestAttributes();
        $this->method = $method;
        $this->uri = $uri;
        $this->headers = $headers;
        $this->body = $body;
        $this->serverParams = $serverParams;
        $this->requestTarget = $requestTarget;
        $this->cookies = $cookies;
        $this->queryParams = $queryParams;
        $this->uploadedFiles = $uploadedFiles;
        $this->parsedBody = $parsedBody;
        $this->attributes = $attributes;
        $this->session = $session;
        $this->protocolVersion = $protocolVersion;
    }

    /** *** PSR-7 Methods *** */

    public function getProtocolVersion(): string
    {
        return $this->protocolVersion;
    }

    public function withProtocolVersion($version): self
    {
        $normalized = (new NormalizeProtocolVersion())->execute(protocol: $version);
        if ($normalized === $this->protocolVersion) {
            return $this;
        }

        return $this->mutate(data: ['protocolVersion' => $normalized]);
    }

    /**
     * @param array<string, mixed> $data
     */
    private function mutate(array $data): self
    {
        return new self(
            body: $data['body'] ?? $this->body,
            method: $data['method'] ?? $this->method,
            uri: $data['uri'] ?? $this->uri,
            headers: $data['headers'] ?? $this->headers,
            serverParams: $data['serverParams'] ?? $this->serverParams,
            requestTarget: array_key_exists('requestTarget', $data) ? $data['requestTarget'] : $this->requestTarget,
            cookies: $data['cookies'] ?? $this->cookies,
            queryParams: $data['queryParams'] ?? $this->queryParams,
            uploadedFiles: $data['uploadedFiles'] ?? $this->uploadedFiles,
            parsedBody: $data['parsedBody'] ?? $this->parsedBody,
            attributes: $data['attributes'] ?? $this->attributes,
            session: array_key_exists('session', $data) ? $data['session'] : $this->session,
            protocolVersion: $data['protocolVersion'] ?? $this->protocolVersion
        );
    }

    public function getHeaders(): array
    {
        return $this->headers->all();
    }

    public function hasHeader($name): bool
    {
        return $this->headers->has(name: $name);
    }

    public function getHeader($name): array
    {
        return $this->headers->get(name: $name);
    }

    public function getHeaderLine($name): string
    {
        return $this->headers->getLine(name: $name);
    }

    public function withAddedHeader($name, $value): self
    {
        return $this->mutate(data: ['headers' => $this->headers->append(name: $name, value: $value)]);
    }

    public function withoutHeader($name): self
    {
        return $this->mutate(data: ['headers' => $this->headers->drop(name: $name)]);
    }

    public function getBody(): StreamInterface
    {
        return $this->body->stream();
    }

    public function withBody(StreamInterface $body): self
    {
        return $this->mutate(data: ['body' => new RequestBody(stream: $body)]);
    }

    public function getRequestTarget(): string
    {
        return (new ReadRequestTarget(explicitTarget: $this->requestTarget, uri: $this->uri))->resolve();
    }

    public function withRequestTarget($requestTarget): self
    {
        return $this->mutate(data: ['requestTarget' => $requestTarget]);
    }

    public function getMethod(): string
    {
        return $this->method;
    }

    public function withMethod($method): self
    {
        return $this->mutate(data: ['method' => $method]);
    }

    public function getUri(): UriInterface
    {
        return $this->uri;
    }

    public function withUri(UriInterface $uri, $preserveHost = false): self
    {
        $newRequest = $this->mutate(data: ['uri' => $uri]);

        if (!$preserveHost || !$this->headers->has(name: 'Host')) {
            $newRequest = $newRequest->withHeader(name: 'Host', value: $uri->getHost());
        }

        return $newRequest;
    }

    public function withHeader($name, $value): self
    {
        return $this->mutate(data: ['headers' => $this->headers->put(name: $name, value: $value)]);
    }

    public function getServerParams(): array
    {
        return $this->serverParams;
    }

    public function getCookieParams(): array
    {
        return $this->cookies->all();
    }

    public function withCookieParams(array $cookies): self
    {
        return $this->mutate(data: ['cookies' => $this->cookies->with(cookies: $cookies)]);
    }

    public function getQueryParams(): array
    {
        return $this->queryParams;
    }

    public function withQueryParams(array $query): self
    {
        return $this->mutate(data: ['queryParams' => $query]);
    }

    public function getUploadedFiles(): array
    {
        return $this->uploadedFiles->all();
    }

    public function withUploadedFiles(array $uploadedFiles): self
    {
        return $this->mutate(data: ['uploadedFiles' => $this->uploadedFiles->with(files: $uploadedFiles)]);
    }

    public function withParsedBody($data): self
    {
        return $this->mutate(data: ['parsedBody' => $this->parsedBody->with(data: $data)]);
    }

    public function getAttributes(): array
    {
        return $this->attributes->all();
    }

    public function getAttribute($name, $default = null): mixed
    {
        return $this->attributes->get(name: $name, default: $default);
    }

    public function withAttribute($name, $value): self
    {
        return $this->mutate(data: ['attributes' => $this->attributes->put(name: $name, value: $value)]);
    }

    public function withoutAttribute($name): self
    {
        return $this->mutate(data: ['attributes' => $this->attributes->drop(name: $name)]);
    }

    /** *** Domain Extensions *** */

    public function inputs(): RequestedInputs
    {
        return new RequestedInputs(queryParams: $this->queryParams, parsedBody: $this->getParsedBody());
    }

    public function getParsedBody(): array|object|null
    {
        return $this->parsedBody->data();
    }

    /** *** Internal Mutation *** */

    public function session(): RequestSession|null
    {
        return $this->session;
    }
}
