<?php

declare(strict_types=1);

namespace Avax\Auth\System\Capability\Identity;

use Avax\Auth\System\Flow\AuthenticateRequest\AuthenticationMode;
use Avax\Auth\System\Flow\Token\IssuedRefreshToken;
use Avax\Auth\System\Flow\Token\IssuedToken;
use DateTimeImmutable;

/**
 * Internal aggregate returned when auth state is established.
 */
final readonly class IssuedAuthentication
{
    public function __construct(
        public AuthenticationMode                             $mode,
        #[\SensitiveParameter] public string|null             $sessionId = null,
        #[\SensitiveParameter] public IssuedToken|null        $accessToken = null,
        #[\SensitiveParameter] public IssuedRefreshToken|null $refreshToken = null,
        public DateTimeImmutable|null                         $mfaVerifiedAt = null,
        public bool                                           $phishingResistant = false
    ) {}
}
