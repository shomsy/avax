<?php

declare(strict_types=1);

namespace Avax\Components\Identity\Auth\System\Capabilities\Identity;

use Avax\Components\Identity\Auth\System\Flows\CheckAuthentication\AuthenticateRequest\AuthenticationMode;
use Avax\Components\Identity\Tokens\System\Capabilities\Tokens\Runtime\Record\IssuedRefreshToken;
use Avax\Components\Identity\Tokens\System\Capabilities\Tokens\Runtime\Record\IssuedToken;
use DateTimeImmutable;
use SensitiveParameter;

/**
 * Internal aggregate returned when auth state is established.
 */
final readonly class IssuedAuthentication
{
    public function __construct(
        public AuthenticationMode $mode,
        #[SensitiveParameter]
        public ?string $sessionId = null,
        #[SensitiveParameter]
        public ?IssuedToken $accessToken = null,
        #[SensitiveParameter]
        public ?IssuedRefreshToken $refreshToken = null,
        public ?DateTimeImmutable $mfaVerifiedAt = null,
        public bool $phishingResistant = false,
    ) {
    }
}
