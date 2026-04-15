<?php

declare(strict_types=1);

namespace Avax\Auth\Integrations\Http;

use SensitiveParameter;

/**
 * Framework-neutral HTTP endpoint snapshot for optional integration adapters.
 */
final readonly class HttpEndpointInput
{
    public array  $server;
    public array  $body;
    public array  $routeParameters;
    public array  $query;
    public array  $headers;
    public string $path;
    public string $method;

    /**
     * @param array<string, mixed> $headers
     * @param array<string, mixed> $query
     * @param array<string, mixed> $routeParameters
     * @param array<string, mixed> $body
     * @param array<string, mixed> $server
     */
    public function __construct(
        string                           $method,
        string                           $path,
        #[SensitiveParameter] array|null $headers = null,
        array|null                       $query = null,
        array|null                       $routeParameters = null,
        array|null                       $body = null,
        array                            $server = []
    )
    {
        $headers               ??= [];
        $query                 ??= [];
        $routeParameters       ??= [];
        $body                  ??= [];
        $this->method          = $method;
        $this->path            = $path;
        $this->headers         = $headers;
        $this->query           = $query;
        $this->routeParameters = $routeParameters;
        $this->body            = $body;
        $this->server          = $server;
    }
}
