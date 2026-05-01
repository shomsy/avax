<?php

declare(strict_types=1);

namespace Avax\Components\Identity\Auth\System\Capabilities\Identity;

use Avax\Components\Identity\Auth\System\Capabilities\Identity\Jwt\JwtIdentityInterface;
use Avax\Components\Identity\Auth\System\Capabilities\Identity\Session\SessionIdentityInterface;
use Avax\Components\Identity\Auth\System\Capabilities\Identity\User\User;
use Avax\Components\Identity\Auth\System\Flows\CheckAuthentication\AuthenticateRequest\AuthenticationContext;
use DateTimeImmutable;

/**
 * Unified authentication identity contract for the Auth System.
 */
interface IdentityInterface
{
    public function issue(
        User $user,
        ?DateTimeImmutable $mfaVerifiedAt = null,
        bool $phishingResistant = false,
    ): IssuedAuthentication;

    public function clear(?AuthenticationContext $context = null): void;

    public function sessionIdentity(): ?SessionIdentityInterface;

    public function jwtIdentity(): ?JwtIdentityInterface;
}
