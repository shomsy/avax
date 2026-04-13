<?php

declare(strict_types=1);

namespace Avax\Auth\Integrations\Http;

/**
 * Framework-neutral input for sender-constrained OAuth verification.
 */
final readonly class HttpOAuthProofInput
{
    /**
     * @param array<string, mixed> $headers
     * @param array<string, mixed> $server
     */
    public function __construct(
        public string $method,
        public string $uri,
        public array $headers = [],
        public array $server = [],
        public string|null $accessToken = null,
        public string|null $expectedTokenThumbprint = null
    ) {}
}
