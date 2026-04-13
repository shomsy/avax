<?php

declare(strict_types=1);

namespace Avax\Auth\Integrations\Http;

use SensitiveParameter;

/**
 * Framework-neutral HTTP snapshot for building the auth ingress request.
 */
final readonly class HttpAuthenticationInput
{
    /**
     * @param array<string, mixed> $headers
     * @param array<string, mixed> $cookies
     * @param array<string, mixed> $server
     */
    public function __construct(
        #[SensitiveParameter] public array       $headers = [],
        public array                             $cookies = [],
        public array                             $server = [],
        public bool                              $allowSession = true,
        #[SensitiveParameter] public string|null $sessionCookieName = null
    ) {}
}
