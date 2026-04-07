<?php

declare(strict_types=1);

namespace Avax\Auth\System\Configuration;

use Avax\Auth\System\Capability\Access\Access;
use Avax\Auth\System\Capability\Access\RequireAuthentication\RequireAuthentication;
use Avax\Auth\System\Capability\Access\RequirePermission\RequirePermission;
use Avax\Auth\System\Capability\Access\RequireRole\RequireRole;
use Avax\Auth\System\Capability\Identity\IdentityInterface;
use Avax\Auth\System\Auth;
use Avax\Auth\System\Flow\ChangePassword\ChangePassword;
use Avax\Auth\System\Flow\CheckAuthentication\CheckAuthentication;
use Avax\Auth\System\Flow\Login\Login;
use Avax\Auth\System\Flow\Logout\Logout;
use Avax\Auth\System\Flow\ReadCurrentUser\ReadCurrentUser;
use Avax\Auth\System\Flow\Register\Register;
use Avax\Auth\System\Capability\PasswordHashing\PasswordHasher;
use Avax\Auth\System\Capability\UserSource\UserSourceInterface;
use Avax\Auth\System\Flow\Login\RateLimit\LoginRateLimit;
use Avax\Auth\System\Foundation\IdGenerator;
use Avax\Auth\System\Foundation\IdGeneratorInterface;

/**
 * Fluent builder for creating Auth system instances.
 *
 * Capability: Composition root for the system.
 */
final class AuthBuilder
{
    private UserSourceInterface|null $userSource = null;
    private IdentityInterface|null    $identity = null;
    private LoginRateLimit|null       $rateLimit = null;
    private PasswordHasher|null       $passwordHasher = null;
    private IdGeneratorInterface|null $idGenerator = null;

    /**
     * Define the data source for users.
     */
    public function forUser(UserSourceInterface $userSource) : self
    {
        $this->userSource = $userSource;
        return $this;
    }

    /**
     * Define the composed identity façade for authentication state.
     */
    public function withIdentity(#[\SensitiveParameter] IdentityInterface $identity) : self
    {
        $this->identity = $identity;
        return $this;
    }

    /**
     * Enable rate limiting for login.
     */
    public function protectFromBruteForce(LoginRateLimit $rateLimit) : self
    {
        $this->rateLimit = $rateLimit;
        return $this;
    }

    /**
     * Configure a custom password hasher.
     */
    public function usingHasher(#[\SensitiveParameter] PasswordHasher $passwordHasher) : self
    {
        $this->passwordHasher = $passwordHasher;
        return $this;
    }

    /**
     * Configure a custom identifier generator for registrations.
     */
    public function usingIdGenerator(IdGeneratorInterface $idGenerator) : self
    {
        $this->idGenerator = $idGenerator;
        return $this;
    }

    /**
     * Build the final Auth instance.
     */
    public function ready() : Auth
    {
        if ($this->userSource === null) {
            throw new \RuntimeException(message: 'Data source is required (forUser).');
        }

        if ($this->identity === null) {
            throw new \RuntimeException(message: 'Identity is required (withIdentity).');
        }

        $identity = $this->identity;
        $passwordHasher = $this->passwordHasher ?? new PasswordHasher();
        $readCurrentUser = new ReadCurrentUser(
            identity: $identity,
            userSource: $this->userSource
        );
        $checkAuthentication = new CheckAuthentication(
            readCurrentUser: $readCurrentUser
        );
        $access = new Access(
            requireAuthentication: new RequireAuthentication(
                checkAuthentication: $checkAuthentication
            ),
            requireRole: new RequireRole(readCurrentUser: $readCurrentUser),
            requirePermission: new RequirePermission(
                readCurrentUser: $readCurrentUser
            )
        );

        return new Auth(
            login: new Login(
                userSource: $this->userSource,
                passwordHasher: $passwordHasher,
                identity: $identity,
                rateLimit: $this->rateLimit
            ),
            logout: new Logout(identity: $identity),
            checkAuthentication: $checkAuthentication,
            readCurrentUser: $readCurrentUser,
            access: $access,
            changePassword: new ChangePassword(
                userSource: $this->userSource,
                passwordHasher: $passwordHasher,
                rateLimit: $this->rateLimit
            ),
            register: new Register(
                userSource: $this->userSource,
                passwordHasher: $passwordHasher,
                idGenerator: $this->idGenerator ?? new IdGenerator(),
                rateLimit: $this->rateLimit
            )
        );
    }
}
