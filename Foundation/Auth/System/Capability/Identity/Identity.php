<?php

declare(strict_types=1);

namespace Avax\Auth\System\Capability\Identity;

use Avax\Auth\System\Capability\Identity\Jwt\JwtIdentityInterface;
use Avax\Auth\System\Capability\Identity\Session\SessionIdentityInterface;
use Avax\Auth\System\Capability\User\User;

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
            throw new \InvalidArgumentException(message: 'Identity requires at least one backend.');
        }
    }

    public function issue(User $user) : string|null
    {
        if (! $user->isActive()) {
            throw new \InvalidArgumentException(message: 'Inactive users cannot be authenticated.');
        }

        $this->sessionIdentity?->issue(userId: $user->getId()->value);

        return $this->jwtIdentity?->issue(user: $user);

    }

    public function authenticate(#[\SensitiveParameter] string $token) : void
    {
        $this->jwtIdentity?->authenticate(token: $token);
    }

    public function clear() : void
    {
        $this->sessionIdentity?->clear();

        $this->jwtIdentity?->clear();
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
