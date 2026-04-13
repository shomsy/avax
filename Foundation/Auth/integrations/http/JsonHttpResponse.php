<?php

declare(strict_types=1);

namespace Avax\Auth\Integrations\Http;

use SensitiveParameter;

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
        public int                         $statusCode,
        public array $body = [],
        #[SensitiveParameter] public array $headers = ['Content-Type' => 'application/json']
    ) {}
}
