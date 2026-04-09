<?php

declare(strict_types=1);

namespace Avax\Auth\Integrations\Http;

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
        public array       $headers = [],
        public array       $cookies = [],
        public array       $server = [],
        public bool        $allowSession = true,
        public string|null $sessionCookieName = null
    ) {}
}
