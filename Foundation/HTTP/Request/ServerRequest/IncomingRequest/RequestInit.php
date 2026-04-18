<?php

declare(strict_types=1);

namespace Avax\HTTP\Request\ServerRequest\IncomingRequest;

use Avax\HTTP\Request\ServerRequest\IncomingRequest\RequestAttributes\RequestAttributes;
use Avax\HTTP\Request\ServerRequest\IncomingRequest\RequestBody\ParsedBody;
use Avax\HTTP\Request\ServerRequest\IncomingRequest\RequestBody\RequestBody;
use Avax\HTTP\Request\ServerRequest\IncomingRequest\RequestCookies\RequestCookies;
use Avax\HTTP\Request\ServerRequest\IncomingRequest\RequestHeaders\RequestHeaders;
use Avax\HTTP\Request\ServerRequest\IncomingRequest\RequestSession\RequestSession;
use Avax\HTTP\Request\ServerRequest\IncomingRequest\RequestTarget\ReadRequestTarget;
use Avax\HTTP\Request\ServerRequest\IncomingRequest\UploadedFiles\UploadedFiles;
use Psr\Http\Message\UriInterface;
use SensitiveParameter;

/**
 * RequestInit - Immutable state container for ServerRequest.
 */
final readonly class RequestInit
{
    public function __construct(
        public RequestBody                          $body,
        public string                               $method,
        public UriInterface                         $uri,
        #[SensitiveParameter] public RequestHeaders $requestHeaders,
        public array                                $serverParams,
        public ?string                              $requestTarget,
        public RequestCookies                       $cookies,
        public array                                $queryParams,
        public UploadedFiles                        $uploadedFiles,
        public ParsedBody                           $parsedBody,
        public RequestAttributes                    $attributes,
        #[SensitiveParameter] public ?RequestSession $session,
        public string                               $protocolVersion,
    ) {}

    public static function fromResolvedParts(
        RequestBody                          $body,
        string                               $method,
        UriInterface                         $uri,
        #[SensitiveParameter] RequestHeaders $requestHeaders,
        array                                $serverParams,
        ?string                              $explicitTarget,
        RequestCookies                       $cookies,
        array                                $queryParams,
        UploadedFiles                        $uploadedFiles,
        ParsedBody                           $parsedBody,
        RequestAttributes                    $attributes,
        #[SensitiveParameter] ?RequestSession $session,
        string                               $protocolVersion,
    ) : self
    {
        $resolver = new ReadRequestTarget(
            explicitTarget: $explicitTarget,
            uri           : $uri,
        );

        return new self(
            body           : $body,
            method         : $method,
            uri            : $uri,
            requestHeaders : $requestHeaders,
            serverParams   : $serverParams,
            requestTarget  : $resolver->resolve(),
            cookies        : $cookies,
            queryParams    : $queryParams,
            uploadedFiles  : $uploadedFiles,
            parsedBody     : $parsedBody,
            attributes     : $attributes,
            session        : $session,
            protocolVersion: $protocolVersion,
        );
    }

    public function withMethod(string $method) : self
    {
        return $this->copy(method: $method);
    }

    public function withUri(UriInterface $uri) : self
    {
        $resolver = new ReadRequestTarget(
            explicitTarget: null,
            uri           : $uri,
        );

        return $this->copy(
            uri          : $uri,
            requestTarget: $resolver->resolve()
        );
    }

    public function withQueryParams(array $queryParams) : self
    {
        return $this->copy(queryParams: $queryParams);
    }

    public function withParsedBody(ParsedBody $parsedBody) : self
    {
        return $this->copy(parsedBody: $parsedBody);
    }

    public function withRequestTarget(string $requestTarget) : self
    {
        return $this->copy(requestTarget: $requestTarget);
    }

    public function withProtocolVersion(string $protocolVersion) : self
    {
        return $this->copy(protocolVersion: $protocolVersion);
    }

    public function withRequestHeaders(#[SensitiveParameter] RequestHeaders $requestHeaders) : self
    {
        return $this->copy(requestHeaders: $requestHeaders);
    }

    public function withBody(RequestBody $body) : self
    {
        return $this->copy(body: $body);
    }

    public function withSession(#[SensitiveParameter] RequestSession $session) : self
    {
        return $this->copy(session: $session);
    }

    public function withAttributes(RequestAttributes $attributes) : self
    {
        return $this->copy(attributes: $attributes);
    }

    public function withCookies(RequestCookies $cookies) : self
    {
        return $this->copy(cookies: $cookies);
    }

    public function withUploadedFiles(UploadedFiles $uploadedFiles) : self
    {
        return $this->copy(uploadedFiles: $uploadedFiles);
    }

    private function copy(
        ?RequestBody      $body = null,
        ?string           $method = null,
        ?UriInterface     $uri = null,
        ?RequestHeaders   $requestHeaders = null,
        ?array            $serverParams = null,
        ?string           $requestTarget = null,
        ?RequestCookies   $cookies = null,
        ?array            $queryParams = null,
        ?UploadedFiles    $uploadedFiles = null,
        ?ParsedBody       $parsedBody = null,
        ?RequestAttributes $attributes = null,
        ?RequestSession   $session = null,
        ?string           $protocolVersion = null,
    ) : self
    {
        return new self(
            body           : $body ?? $this->body,
            method         : $method ?? $this->method,
            uri            : $uri ?? $this->uri,
            requestHeaders : $requestHeaders ?? $this->requestHeaders,
            serverParams   : $serverParams ?? $this->serverParams,
            requestTarget  : $requestTarget ?? $this->requestTarget,
            cookies        : $cookies ?? $this->cookies,
            queryParams    : $queryParams ?? $this->queryParams,
            uploadedFiles  : $uploadedFiles ?? $this->uploadedFiles,
            parsedBody     : $parsedBody ?? $this->parsedBody,
            attributes     : $attributes ?? $this->attributes,
            session        : $session ?? $this->session,
            protocolVersion: $protocolVersion ?? $this->protocolVersion,
        );
    }
}
