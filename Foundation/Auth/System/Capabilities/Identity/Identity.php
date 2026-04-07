<?php

declare(strict_types=1);

namespace Avax\Auth\System\Capabilities\Identity;

use Avax\Auth\System\Capabilities\Identity\Jwt\JwtIdentityInterface;
use Avax\Auth\System\Capabilities\Identity\Session\SessionIdentityInterface;
use Avax\Auth\System\Capabilities\User\User;

/**
 * Unified identity façade that coordinates session and JWT authentication state.
 */
final readonly class Identity implements IdentityInterface
{
    public function __construct(
        #[\SensitiveParameter] private SessionIdentityInterface|null $sessionIdentity = null,
        #[\SensitiveParameter] private JwtIdentityInterface|null     $jwtIdentity = null
    ) {
        if ($this->sessionIdentity === null && $this->jwtIdentity === null) {
            throw new \InvalidArgumentException('Identity requires at least one backend.');
        }
    }

    public function issue(User $user) : string|null
    {
        if (! $user->isActive()) {
            throw new \InvalidArgumentException('Inactive users cannot be authenticated.');
        }

        if ($this->sessionIdentity !== null) {
            $this->sessionIdentity->issue(userId: $user->getId()->value);
        }

        if ($this->jwtIdentity !== null) {
            return $this->jwtIdentity->issue(user: $user);
        }

        return null;
    }

    public function authenticate(#[\SensitiveParameter] string $token) : void
    {
        if ($this->jwtIdentity !== null) {
            $this->jwtIdentity->authenticate(token: $token);
        }
    }

    public function clear() : void
    {
        if ($this->sessionIdentity !== null) {
            $this->sessionIdentity->clear();
        }

        if ($this->jwtIdentity !== null) {
            $this->jwtIdentity->clear();
        }
    }

    public function check() : bool
    {
        if ($this->sessionIdentity?->check() === true) {
            return true;
        }

        return $this->jwtIdentity?->check() ?? false;
    }

    public function token() : string|null
    {
        return $this->jwtIdentity?->token();
    }

    public function getCurrentUser() : User|null
    {
        return $this->jwtIdentity?->getCurrentUser();
    }

    public function getUserId() : int|null
    {
        if ($this->sessionIdentity !== null) {
            $sessionUserId = $this->sessionIdentity->getUserId();

            if ($sessionUserId !== null) {
                return $sessionUserId;
            }
        }

        return $this->jwtIdentity?->getCurrentUser()?->getId()->value;
    }
}
