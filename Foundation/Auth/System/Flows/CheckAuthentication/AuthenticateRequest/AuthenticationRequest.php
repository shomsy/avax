<?php

declare(strict_types=1);

namespace Avax\Auth\System\Flows\AuthenticateRequest;

use SensitiveParameter;

/**
 * Immutable request snapshot for auth ingress resolution.
 */
final readonly class AuthenticationRequest
{
    public string|null $userAgent;
    public string|null $ipAddress;
    public bool        $allowSession;
    public string|null $bearerToken;

    public function __construct(
        #[SensitiveParameter] string|null $bearerToken = null,
        bool|null                         $allowSession = null,
        #[SensitiveParameter] string|null $ipAddress = null,
        string|null                       $userAgent = null
    )
    {
        $allowSession       ??= true;
        $this->bearerToken  = $bearerToken;
        $this->allowSession = $allowSession;
        $this->ipAddress    = $ipAddress;
        $this->userAgent    = $userAgent;
    }

    public static function bearer(
        #[SensitiveParameter] string      $bearerToken,
        #[SensitiveParameter] string|null $ipAddress = null,
        string|null                       $userAgent = null
    ) : self
    {
        return new self(
            bearerToken: $bearerToken,
            ipAddress  : $ipAddress,
            userAgent  : $userAgent
        );
    }
}
