<?php

declare(strict_types=1);

namespace Avax\Components\Identity\Auth\System\Flows\CheckAuthentication\AuthenticateRequest;

use SensitiveParameter;

/**
 * Immutable request snapshot for auth ingress resolution.
 */
final readonly class AuthenticationRequest
{
    public bool $allowSession;

    public function __construct(
        #[SensitiveParameter]
        public string|null $bearerToken = null,
        bool|null $allowSession = null,
        #[SensitiveParameter]
        public string|null $ipAddress = null,
        public string|null $userAgent = null,
    ) {
        $allowSession ??= true;
        $this->allowSession = $allowSession;
    }

    public static function bearer(
        #[SensitiveParameter]
        string $bearerToken,
        #[SensitiveParameter]
        string|null $ipAddress = null,
        string|null $userAgent = null,
    ) : self
    {
        return new self(
            bearerToken: $bearerToken,
            ipAddress  : $ipAddress,
            userAgent  : $userAgent,
        );
    }
}
