<?php

declare(strict_types=1);

namespace Avax\Auth\System\Capability\Identity;

use Avax\Auth\System\Capability\Identity\Jwt\JwtIdentityInterface;
use Avax\Auth\System\Capability\Identity\Session\SessionIdentityInterface;
use Avax\Auth\System\Capability\User\User;
use Avax\Auth\System\Flow\AuthenticateRequest\AuthenticationContext;
use DateTimeImmutable;

/**
 * Unified authentication identity contract for the Auth System.
 */
interface IdentityInterface
{
    public function issue(
        User $user,
        DateTimeImmutable|null $mfaVerifiedAt = null,
        bool $phishingResistant = false
    ) : IssuedAuthentication;

    public function clear(AuthenticationContext|null $context = null) : void;

    public function sessionIdentity() : SessionIdentityInterface|null;

    public function jwtIdentity() : JwtIdentityInterface|null;
}
