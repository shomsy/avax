<?php

declare(strict_types=1);

namespace Avax\Auth\System\Capabilities\Identity;

use Avax\Auth\System\Capabilities\Identity\Tokens\Runtime\Record\IssuedRefreshToken;
use Avax\Auth\System\Capabilities\Identity\Tokens\Runtime\Record\IssuedToken;
use Avax\Auth\System\Flows\CheckAuthentication\AuthenticateRequest\AuthenticationMode;
use DateTimeImmutable;
use SensitiveParameter;

/**
 * Internal aggregate returned when auth state is established.
 */
final readonly class IssuedAuthentication
{
    public bool                    $phishingResistant;
    public DateTimeImmutable|null  $mfaVerifiedAt;
    public IssuedRefreshToken|null $refreshToken;
    public IssuedToken|null        $accessToken;
    public string|null             $sessionId;
    public AuthenticationMode      $mode;

    public function __construct(
        AuthenticationMode                            $mode,
        #[SensitiveParameter] string|null             $sessionId = null,
        #[SensitiveParameter] IssuedToken|null        $accessToken = null,
        #[SensitiveParameter] IssuedRefreshToken|null $refreshToken = null,
        DateTimeImmutable|null                        $mfaVerifiedAt = null,
        bool                                          $phishingResistant = false
    )
    {
        $this->mode              = $mode;
        $this->sessionId         = $sessionId;
        $this->accessToken       = $accessToken;
        $this->refreshToken      = $refreshToken;
        $this->mfaVerifiedAt     = $mfaVerifiedAt;
        $this->phishingResistant = $phishingResistant;
    }
}
