<?php

declare(strict_types=1);

namespace Avax\Auth\Integrations\Http;

use SensitiveParameter;

/**
 * Framework-neutral input for sender-constrained OAuth verification.
 */
final readonly class HttpOAuthProofInput
{
    public string|null $expectedTokenThumbprint;
    public string|null $accessToken;
    public array       $server;
    public array       $headers;
    public string      $uri;
    public string      $method;

    /**
     * @param array<string, mixed> $headers
     * @param array<string, mixed> $server
     */
    public function __construct(
        string                            $method,
        string                            $uri,
        #[SensitiveParameter] array|null  $headers = null,
        array|null                        $server = null,
        #[SensitiveParameter] string|null $accessToken = null,
        #[SensitiveParameter] string|null $expectedTokenThumbprint = null
    )
    {
        $headers                       ??= [];
        $server                        ??= [];
        $this->method                  = $method;
        $this->uri                     = $uri;
        $this->headers                 = $headers;
        $this->server                  = $server;
        $this->accessToken             = $accessToken;
        $this->expectedTokenThumbprint = $expectedTokenThumbprint;
    }
}
