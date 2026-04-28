<?php

declare(strict_types=1);

namespace Avax\Components\Auth\System\PublicSurface;

use Avax\Components\Auth\System\Capabilities\Access\Access;
use Avax\Components\Auth\System\Capabilities\Identity\Identity;
use Avax\Components\Auth\System\Capabilities\Identity\IdentityInterface;
use Avax\Components\Auth\System\Capabilities\Identity\User\User;
use Avax\Components\Auth\System\Flows\Login\Credentials;
use Throwable;

/**
 * Public surface for the Auth component.
 * Backed by the powerful Identity capability and Access control.
 */
final class Auth implements AuthInterface
{
    public function __construct(
        private readonly IdentityInterface $identity,
        private readonly Access            $access
    ) {}

    public function user() : ?User
    {
        return $this->identity->authentication()->user();
    }

    public function check() : bool
    {
        return $this->identity->authentication()->check();
    }

    public function guest() : bool
    {
        return $this->identity->authentication()->guest();
    }

    public function login(array $credentials) : bool
    {
        try {
            $this->identity->login(new Credentials($credentials['email'], $credentials['password']));

            return true;
        } catch (Throwable) {
            return false;
        }
    }

    public function logout() : void
    {
        $this->identity->logout();
    }

    public function cannot(string $permission, mixed $resource = null) : bool
    {
        return ! $this->can($permission, $resource);
    }

    public function can(string $permission, mixed $resource = null) : bool
    {
        return $this->access->access()->allows($permission, $resource);
    }

    public function access() : Access
    {
        return $this->access;
    }

    public function identity() : IdentityInterface
    {
        return $this->identity;
    }
}