<?php

declare(strict_types=1);

namespace Avax\Auth\System\Flow\AuthenticateRequest;

use SensitiveParameter;

/**
 * Immutable request snapshot for auth ingress resolution.
 */
final readonly class AuthenticationRequest
{
    public function __construct(
        #[SensitiveParameter] public string|null $bearerToken = null,
        public bool                              $allowSession = true,
        public string|null                       $ipAddress = null,
        public string|null                       $userAgent = null
    ) {}

    public static function bearer(
        #[SensitiveParameter] string $bearerToken,
        string|null                  $ipAddress = null,
        string|null                  $userAgent = null
    ) : self
    {
        return new self(
            bearerToken: $bearerToken,
            ipAddress  : $ipAddress,
            userAgent  : $userAgent
        );
    }
}
