<?php

declare(strict_types=1);

namespace Avax\Auth\Integrations\Http;

/**
 * JSON-ready transport response for optional HTTP adapters.
 */
final readonly class JsonHttpResponse
{
    /**
     * @param array<string, mixed>|list<mixed> $body
     * @param array<string, string> $headers
     */
    public function __construct(
        public int $statusCode,
        public array $body = [],
        public array $headers = ['Content-Type' => 'application/json']
    ) {}
}
