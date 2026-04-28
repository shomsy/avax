<?php

declare(strict_types=1);

namespace Avax\Components\Identity\Auth\Integrations\Http;

use SensitiveParameter;

/**
 * Framework-neutral HTTP endpoint snapshot for optional integration adapters.
 */
final readonly class HttpEndpointInput
{
    /** @var array<string, mixed> */
    public array $body;
    /** @var array<string, mixed> */
    public array $routeParameters;
    /** @var array<string, mixed> */
    public array $query;
    /** @var array<string, mixed> */
    public array $headers;

    /**
     * @param array<string, mixed> $headers
     * @param array<string, mixed> $query
     * @param array<string, mixed> $routeParameters
     * @param array<string, mixed> $body
     * @param array<string, mixed> $server
     */
    public function __construct(
        public string                    $method,
        public string                    $path,
        #[SensitiveParameter] array|null $headers = null,
        array|null                       $query = null,
        array|null                       $routeParameters = null,
        array|null                       $body = null,
        public array                     $server = []
    )
    {
        $headers               ??= [];
        $query                 ??= [];
        $routeParameters       ??= [];
        $body                  ??= [];
        $this->headers         = $headers;
        $this->query           = $query;
        $this->routeParameters = $routeParameters;
        $this->body            = $body;
    }
}
