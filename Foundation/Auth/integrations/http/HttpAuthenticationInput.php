<?php

declare(strict_types=1);

namespace Avax\Auth\Integrations\Http;

use SensitiveParameter;

/**
 * Framework-neutral HTTP snapshot for building the auth ingress request.
 */
final readonly class HttpAuthenticationInput
{
    public string|null $sessionCookieName;
    public bool        $allowSession;
    public array       $server;
    public array       $cookies;
    public array       $headers;

    /**
     * @param array<string, mixed> $headers
     * @param array<string, mixed> $cookies
     * @param array<string, mixed> $server
     */
    public function __construct(
        #[SensitiveParameter] array|null  $headers = null,
        array|null                        $cookies = null,
        array|null                        $server = null,
        bool|null                         $allowSession = null,
        #[SensitiveParameter] string|null $sessionCookieName = null
    )
    {
        $headers                 ??= [];
        $cookies                 ??= [];
        $server                  ??= [];
        $allowSession            ??= true;
        $this->headers           = $headers;
        $this->cookies           = $cookies;
        $this->server            = $server;
        $this->allowSession      = $allowSession;
        $this->sessionCookieName = $sessionCookieName;
    }
}
