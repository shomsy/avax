<?php

declare(strict_types=1);

namespace Avax\HTTP\Request\IncomingHttp\IncomingRequest\Configuration;

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
 * Configuration Owner: Orchestrates the creation of a ServerRequest object from the current HTTP environment.
 */
final readonly class CreateRequestFromIncomingHttp
{
    public function execute() : ServerRequest
    {
        $server = $_SERVER;

        // 1. Resolve URI
        $uri = $this->createUri(server: $server);

        // 2. Pre-process headers
        $headers        = $this->extractHeaders(server: $server);
        $requestHeaders = new RequestHeaders(headers: $headers);

        // 3. Resolve body
        $bodyStream = new Stream(stream: fopen('php://input', 'r'));
        $body       = new RequestBody(stream: $bodyStream);

        // 4. Parse body if needed
        $contentType    = $requestHeaders->getLine(name: 'Content-Type');
        $parsedBodyData = (new ParseBodyByContentType())->execute(contentType: $contentType, content: $body->content());
        $parsedBody     = new ParsedBody(data: $parsedBodyData);

        // 5. Build ServerRequest
        return new ServerRequest(
            body           : $body,
            method         : $server['REQUEST_METHOD'] ?? 'GET',
            uri            : $uri,
            headers        : $requestHeaders,
            serverParams   : $server,
            cookies        : new RequestCookies(cookies: $_COOKIE),
            // attach session if available
            // session: new RequestSession(data: $_SESSION ?? []),
            queryParams    : $_GET,
            uploadedFiles  : new UploadedFiles(files: (new NormalizeUploadedFiles())->execute(files: $_FILES)),
            parsedBody     : $parsedBody,
            protocolVersion: $server['SERVER_PROTOCOL'] ?? '1.1'
        );
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
