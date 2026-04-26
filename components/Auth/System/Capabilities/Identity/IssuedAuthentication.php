<?php

declare(strict_types=1);

namespace components\Auth\System\Capabilities\Identity;

use components\Auth\System\Capabilities\Identity\Tokens\Runtime\Record\IssuedRefreshToken;
use components\Auth\System\Capabilities\Identity\Tokens\Runtime\Record\IssuedToken;
use components\Auth\System\Flows\CheckAuthentication\AuthenticateRequest\AuthenticationMode;
use DateTimeImmutable;
use SensitiveParameter;

/**
 * Internal aggregate returned when auth state is established.
 */
final readonly class IssuedAuthentication
{
    public function __construct(
        public AuthenticationMode                            $mode,
        #[SensitiveParameter] public string|null             $sessionId = null,
        #[SensitiveParameter] public IssuedToken|null        $accessToken = null,
        #[SensitiveParameter] public IssuedRefreshToken|null $refreshToken = null,
        public DateTimeImmutable|null                        $mfaVerifiedAt = null,
        public bool                                          $phishingResistant = false
    ) {}
}
