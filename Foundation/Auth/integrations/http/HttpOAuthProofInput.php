<?php

declare(strict_types=1);

namespace Avax\Auth\Integrations\Http;

use SensitiveParameter;

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
        public string                            $method,
        public string                            $uri,
        #[SensitiveParameter] public array       $headers = [],
        public array                             $server = [],
        #[SensitiveParameter] public string|null $accessToken = null,
        #[SensitiveParameter] public string|null $expectedTokenThumbprint = null
    ) {}
}
