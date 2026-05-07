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
        public ?string $bearerToken = null,
        ?bool          $allowSession = null,
        #[SensitiveParameter]
        public ?string $ipAddress = null,
        public ?string $userAgent = null,
    )
    {
        $allowSession       ??= true;
        $this->allowSession = $allowSession;
    }

    public static function bearer(
        #[SensitiveParameter]
        string  $bearerToken,
        #[SensitiveParameter]
        ?string $ipAddress = null,
        ?string $userAgent = null,
    ) : self
    {
        return new self(
            bearerToken: $bearerToken,
            ipAddress  : $ipAddress,
            userAgent  : $userAgent,
        );
    }
}
