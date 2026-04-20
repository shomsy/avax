<?php

declare(strict_types=1);

namespace Avax\Auth\System\Capabilities\Identity;

use Avax\Auth\System\Capabilities\Identity\Jwt\JwtIdentityInterface;
use Avax\Auth\System\Capabilities\Identity\Session\SessionIdentityInterface;
use Avax\Auth\System\Capabilities\User\User;
use Avax\Auth\System\Flows\AuthenticateRequest\AuthenticationContext;
use DateTimeImmutable;

/**
 * Unified authentication identity contract for the Auth System.
 */
interface IdentityInterface
{
    public function issue(
        User                   $user,
        DateTimeImmutable|null $mfaVerifiedAt = null,
        bool                   $phishingResistant = false
    ) : IssuedAuthentication;

    public function clear(AuthenticationContext|null $context = null) : void;

    public function sessionIdentity() : SessionIdentityInterface|null;

    public function jwtIdentity() : JwtIdentityInterface|null;
}
