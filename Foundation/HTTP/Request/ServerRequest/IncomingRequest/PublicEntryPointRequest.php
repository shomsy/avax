<?php

declare(strict_types=1);

namespace Avax\HTTP\Request\ServerRequest\IncomingRequest;

use Avax\HTTP\Request\ServerRequest\IncomingRequest\RequestBody\ParsedBody;

/**
 * Public Entry Point: Static factory for creating ServerRequest instances.
 */
final readonly class PublicEntryPointRequest
{
    /**
     * Captures the current HTTP environment and returns a new ServerRequest.
     */
    public static function fromIncomingHttp(): ServerRequest
    {
        return ServerRequest::create(
            init: RequestInit::fromGlobals()
        );
    }

    /**
     * Create a ServerRequest from superglobals arrays.
     */
    public static function fromGlobals(
        array|null $server = null,
        array|null $query = null,
        array|null $cookie = null,
        array|null $files = null,
    ): ServerRequest {
        return ServerRequest::create(
            init: RequestInit::fromGlobals(
                server: $server,
                query: $query,
                cookie: $cookie,
                files: $files,
            )
        );
    }

    /**
     * Create a ServerRequest from query params and parsed body.
     */
    public static function fromSlices(
        array|null        $queryParams = null,
        array|object|null $parsedBody = null,
        string            $method = 'GET',
    ): ServerRequest {
        return ServerRequest::create(
            init: RequestInit::fromSlices(
                queryParams: $queryParams,
                parsedBody: $parsedBody,
                method: $method,
            )
        );
    }

    /**
     * Create a ServerRequest from an array of merged data.
     */
    public static function fromArray(array $data): ServerRequest
    {
        return ServerRequest::create(
            init: RequestInit::defaults()
                ->withQueryParams(queryParams: $data)
                ->withParsedBody(parsedBody: new ParsedBody(data: $data))
        );
    }

    /**
     * Create an empty ServerRequest with default values.
     */
    public static function empty(): ServerRequest
    {
        return ServerRequest::create(
            init: RequestInit::defaults()
        );
    }
}
