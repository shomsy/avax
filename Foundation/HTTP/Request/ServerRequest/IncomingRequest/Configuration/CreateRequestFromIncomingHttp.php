<?php

declare(strict_types=1);

namespace Avax\HTTP\Request\IncomingHttp\IncomingRequest\Configuration;

use Avax\HTTP\Request\IncomingHttp\IncomingRequest\ProtocolVersion\NormalizeProtocolVersion;
use Avax\HTTP\Request\IncomingHttp\IncomingRequest\RequestBody\ParsedBody;
use Avax\HTTP\Request\IncomingHttp\IncomingRequest\RequestBody\Parsers\ParseBodyByContentType;
use Avax\HTTP\Request\IncomingHttp\IncomingRequest\RequestBody\RequestBody;
use Avax\HTTP\Request\IncomingHttp\IncomingRequest\RequestCookies\RequestCookies;
use Avax\HTTP\Request\IncomingHttp\IncomingRequest\RequestHeaders\RequestHeaders;
use Avax\HTTP\Request\IncomingHttp\IncomingRequest\ServerRequest;
use Avax\HTTP\Request\IncomingHttp\IncomingRequest\UploadedFiles\NormalizeUploadedFiles;
use Avax\HTTP\Request\IncomingHttp\IncomingRequest\UploadedFiles\UploadedFiles;
use Avax\HTTP\Response\Classes\Stream;
use Avax\HTTP\URI\UriBuilder;

/**
 * Configuration Owner: Orchestrates the creation of a ServerRequest object.
 */
final readonly class CreateRequestFromIncomingHttp
{
    /**
     * Entry point using superglobals.
     */
    public static function capture() : ServerRequest
    {
        return (new self())->execute(
            server: $_SERVER,
            query : $_GET,
            cookie: $_COOKIE,
            files : $_FILES
        );
    }

    /**
     * Core creation logic decoupled from superglobals.
     */
    public function execute(
        array $server,
        array $query = [],
        array $cookie = [],
        array $files = [],
        mixed $body = null
    ) : ServerRequest
    {
        // 1. Resolve URI
        $uri = $this->createUri(server: $server);

        // 2. Pre-process headers
        $headers        = $this->extractHeaders(server: $server);
        $requestHeaders = new RequestHeaders(headers: $headers);

        // 3. Resolve body
        $bodyOwner = $this->resolveBody(body: $body);

        // 4. Parse body if needed
        $contentType    = $requestHeaders->getLine(name: 'Content-Type');
        $parsedBodyData = (new ParseBodyByContentType())->execute(contentType: $contentType, content: $bodyOwner->content());
        $parsedBody     = new ParsedBody(data: $parsedBodyData);

        // 5. Build ServerRequest
        return new ServerRequest(
            body           : $bodyOwner,
            method         : $server['REQUEST_METHOD'] ?? 'GET',
            uri            : $uri,
            headers        : $requestHeaders,
            serverParams   : $server,
            cookies        : new RequestCookies(cookies: $cookie),
            queryParams    : $query,
            uploadedFiles  : new UploadedFiles(files: (new NormalizeUploadedFiles())->execute(files: $files)),
            parsedBody     : $parsedBody,
            protocolVersion: (new NormalizeProtocolVersion())->execute(protocol: $server['SERVER_PROTOCOL'] ?? '1.1')
        );
    }

    private function resolveBody(mixed $body) : RequestBody
    {
        if ($body instanceof RequestBody) {
            return $body;
        }

        if ($body instanceof \Psr\Http\Message\StreamInterface) {
            return new RequestBody(stream: $body);
        }

        $streamHandle = fopen('php://input', 'r');
        
        // Safety check for fopen
        if ($streamHandle === false) {
             $streamHandle = fopen('php://temp', 'r+');
        }

        return new RequestBody(stream: new Stream(stream: $streamHandle));
    }

    private function createUri(array $server) : UriBuilder
    {
        $protocol = (empty($server['HTTPS']) || $server['HTTPS'] === 'off') ? 'http' : 'https';
        $host     = $server['HTTP_HOST'] ?? 'localhost';
        $uri      = $server['REQUEST_URI'] ?? '/';

        return UriBuilder::createFromString(uri: "$protocol://$host$uri");
    }

    private function extractHeaders(array $server) : array
    {
        $headers = [];
        foreach ($server as $key => $value) {
            if (str_starts_with($key, 'HTTP_')) {
                $name           = str_replace('_', '-', substr($key, 5));
                $headers[$name] = $value;
            } elseif (in_array($key, ['CONTENT_TYPE', 'CONTENT_LENGTH'])) {
                $name           = str_replace('_', '-', $key);
                $headers[$name] = $value;
            }
        }

        return $headers;
    }
}
