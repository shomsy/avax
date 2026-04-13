<?php

declare(strict_types=1);

namespace Avax\Auth\Integrations\Http;

/**
 * Framework-neutral HTTP endpoint snapshot for optional integration adapters.
 */
final readonly class HttpEndpointInput
{
    /**
     * @param array<string, mixed> $headers
     * @param array<string, mixed> $query
     * @param array<string, mixed> $routeParameters
     * @param array<string, mixed> $body
     * @param array<string, mixed> $server
     */
    public function __construct(
        public string $method,
        public string $path,
        public array $headers = [],
        public array $query = [],
        public array $routeParameters = [],
        public array $body = [],
        public array $server = []
    ) {}
}
