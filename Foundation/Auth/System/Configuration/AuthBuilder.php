<?php

declare(strict_types=1);

namespace Avax\Auth\System\Configuration;

use Avax\Auth\System\Auth;
use Avax\Auth\System\Capabilities\Identity\Identity;
use Avax\Auth\System\Flows\Login\Login;
use Avax\Auth\System\Flows\Logout\Logout;
use Avax\Auth\System\Flows\Register\Register;
use Avax\Auth\System\Flows\ChangePassword\ChangePassword;
use Avax\Auth\System\Flows\CheckAuthentication\CheckAuthentication;
use Avax\Auth\System\Flows\ReadCurrentUser\ReadCurrentUser;
use Avax\Auth\System\Capabilities\Identity\Session\SessionIdentityInterface;
use Avax\Auth\System\Capabilities\Identity\Jwt\JwtIdentityInterface;
use Avax\Auth\System\Capabilities\UserSource\UserSourceInterface;
use Avax\Auth\System\Capabilities\PasswordHashing\PasswordHasher;
use Avax\Auth\System\Flows\Login\RateLimit\LoginRateLimit;
use Avax\Auth\System\Foundation\IdGenerator;

/**
 * Fluent builder for creating Auth system instances.
 *
 * Capability: Composition root for the system.
 */
final class AuthBuilder
{
    private UserSourceInterface|null  $userSource      = null;
    private SessionIdentityInterface|null $sessionIdentity = null;
    private JwtIdentityInterface|null $jwtIdentity     = null;
    private LoginRateLimit|null       $rateLimit       = null;
    private PasswordHasher|null       $passwordHasher  = null;

    /**
     * Define the data source for users.
     */
    public function forUser(UserSourceInterface $userSource) : self
    {
        $this->userSource = $userSource;
        return $this;
    }

    /**
     * Enable session identification.
     */
    public function withSession(#[\SensitiveParameter] SessionIdentityInterface $sessionIdentity) : self
    {
        $this->sessionIdentity = $sessionIdentity;
        return $this;
    }

    /**
     * Enable JWT identification.
     */
    public function withJwt(#[\SensitiveParameter] JwtIdentityInterface $jwtIdentity) : self
    {
        $this->jwtIdentity = $jwtIdentity;
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
     * Build the final Auth instance.
     */
    public function ready() : Auth
    {
        if ($this->userSource === null) {
            throw new \RuntimeException('Data source is required (forUser).');
        }

        if ($this->sessionIdentity === null && $this->jwtIdentity === null) {
            throw new \RuntimeException('At least one identity adapter is required.');
        }

        $passwordHasher = $this->passwordHasher ?? new PasswordHasher();
        $identity = new Identity(
            sessionIdentity: $this->sessionIdentity,
            jwtIdentity: $this->jwtIdentity
        );

        return new Auth(
            login: new Login(
                userSource: $this->userSource,
                passwordHasher: $passwordHasher,
                identity: $identity,
                rateLimit: $this->rateLimit
            ),
            logout: new Logout(identity: $identity),
            checkAuthentication: new CheckAuthentication(identity: $identity),
            readCurrentUser: new ReadCurrentUser(
                identity: $identity,
                userSource: $this->userSource
            ),
            changePassword: new ChangePassword(
                userSource: $this->userSource,
                passwordHasher: $passwordHasher
            ),
            register: new Register(
                userSource: $this->userSource,
                passwordHasher: $passwordHasher,
                idGenerator: new IdGenerator()
            )
        );
    }
}
