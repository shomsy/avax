<?php

declare(strict_types=1);

namespace Avax\Auth\Integrations\Http;

use SensitiveParameter;

/**
 * Framework-neutral HTTP snapshot for building the auth ingress request.
 */
final readonly class HttpAuthenticationInput
{
    public bool        $allowSession;
    /** @var array<string, mixed> */
    public array       $server;
    /** @var array<string, mixed> */
    public array       $cookies;
    /** @var array<string, mixed> */
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
        #[SensitiveParameter] public string|null $sessionCookieName = null
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
    }
}
