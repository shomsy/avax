<?php

declare(strict_types=1);

namespace Avax\Framework\System\Flows\HandleIncomingHttp;

use Avax\Components\HTTP\Request\ServerRequest\IncomingRequest\ServerRequest;
use Avax\Framework\System\Capabilities\Runtime\RuntimeRequest;
use GuzzleHttp\Psr7\Uri;
use GuzzleHttp\Psr7\Utils;
use Psr\Http\Message\UriInterface;

/**
 * Converts a lightweight RuntimeRequest into a full ServerRequest.
 *
 * This is the framework-level bridge between the runtime adapter
 * (PHP built-in server, Swoole, RoadRunner, etc.) and the HTTP
 * component's PSR-7 ServerRequest.
 */
final readonly class ReadIncomingHttpRequest
{
    public function read(RuntimeRequest $runtimeRequest) : ServerRequest
    {
        $uri          = new Uri(uri: $this->normalizeUri(uri: $runtimeRequest->uri()));
        $body         = $runtimeRequest->body() ?? '';
        $queryParams  = $this->parseQueryParams(uri: $uri);
        $parsedBody   = $this->parseBody(headers: $runtimeRequest->headers(), body: $body);

        return new ServerRequest(
            serverParams   : $this->buildServerParams(uri: $uri, runtimeRequest: $runtimeRequest),
            queryParams    : $queryParams,
            parsedBody     : $parsedBody,
            method         : $runtimeRequest->method(),
            uri            : $uri,
            headers        : $this->flattenHeaders(headers: $runtimeRequest->headers()),
            stream         : Utils::streamFor($body),
        );
    }

    private function normalizeUri(string $uri) : string
    {
        if (str_contains(haystack: $uri, needle: '://')) {
            return $uri;
        }

        $normalizedPath = str_starts_with(haystack: $uri, needle: '/')
            ? $uri
            : '/' . ltrim(string: $uri, characters: '/');

        return 'http://localhost' . $normalizedPath;
    }

    /**
     * @param array<string, list<string>> $headers
     * @return array<mixed>|null
     */
    private function parseBody(array $headers, string $body) : ?array
    {
        if ($body === '') {
            return null;
        }

        $contentType = $this->headerLine(headers: $headers, name: 'Content-Type');

        if (str_contains(haystack: $contentType, needle: 'application/json')) {
            $decoded = json_decode(json: $body, associative: true);

            return is_array(value: $decoded) ? $decoded : null;
        }

        if (str_contains(haystack: $contentType, needle: 'application/x-www-form-urlencoded')) {
            $result = [];
            parse_str(string: $body, result: $result);

            return $result;
        }

        return null;
    }

    /**
     * @return array<mixed>
     */
    private function parseQueryParams(Uri $uri) : array
    {
        $queryParams = [];
        parse_str(string: $uri->getQuery(), result: $queryParams);

        return $queryParams;
    }

    /**
     * @param array<string, list<string>> $headers
     * @return array<string, string>
     */
    private function flattenHeaders(array $headers) : array
    {
        $flat = [];
        foreach ($headers as $name => $values) {
            $flat[$name] = implode(separator: ', ', array: $values);
        }

        return $flat;
    }

    /**
     * @return array<string, mixed>
     */
    private function buildServerParams(Uri $uri, RuntimeRequest $runtimeRequest) : array
    {
        $serverParams = [
            'REQUEST_METHOD' => $runtimeRequest->method(),
            'REQUEST_URI'    => $uri->getPath() . ($uri->getQuery() === '' ? '' : '?' . $uri->getQuery()),
            'HTTP_HOST'      => $uri->getHost() === '' ? 'localhost' : $uri->getHost(),
        ];

        if ($uri->getScheme() === 'https') {
            $serverParams['HTTPS'] = 'on';
        }

        foreach ($runtimeRequest->headers() as $name => $values) {
            $serverKey = strtoupper(string: str_replace(search: '-', replace: '_', subject: $name));

            if ($serverKey === 'CONTENT_TYPE' || $serverKey === 'CONTENT_LENGTH') {
                $serverParams[$serverKey] = implode(separator: ',', array: $values);

                continue;
            }

            $serverParams['HTTP_' . $serverKey] = implode(separator: ',', array: $values);
        }

        return $serverParams;
    }

    /**
     * @param array<string, list<string>> $headers
     */
    private function headerLine(array $headers, string $name) : string
    {
        foreach ($headers as $headerName => $values) {
            if (strcasecmp(string1: $headerName, string2: $name) !== 0) {
                continue;
            }

            return implode(separator: ',', array: $values);
        }

        return '';
    }
}
