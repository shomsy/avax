<?php

declare(strict_types=1);

namespace Avax\Components\Identity\Auth\System\Capabilities\Identity;

use Avax\Components\Identity\Auth\System\Capabilities\Identity\Tokens\Runtime\Record\IssuedRefreshToken;
use Avax\Components\Identity\Auth\System\Capabilities\Identity\Tokens\Runtime\Record\IssuedToken;
use Avax\Components\Identity\Auth\System\Flows\CheckAuthentication\AuthenticateRequest\AuthenticationMode;
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
