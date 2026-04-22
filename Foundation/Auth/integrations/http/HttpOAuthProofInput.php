<?php

declare(strict_types=1);

namespace Avax\Auth\Integrations\Http;

use SensitiveParameter;

/**
 * Framework-neutral input for sender-constrained OAuth verification.
 */
final readonly class HttpOAuthProofInput
{
    /** @var array<string, mixed> */
    public array       $server;
    /** @var array<string, mixed> */
    public array       $headers;

    /**
     * @param array<string, mixed> $headers
     * @param array<string, mixed> $server
     */
    public function __construct(
        public string                            $method,
        public string                            $uri,
        #[SensitiveParameter] array|null  $headers = null,
        array|null                        $server = null,
        #[SensitiveParameter] public string|null $accessToken = null,
        #[SensitiveParameter] public string|null $expectedTokenThumbprint = null
    )
    {
        $headers                       ??= [];
        $server                        ??= [];
        $this->headers                 = $headers;
        $this->server                  = $server;
    }
}
