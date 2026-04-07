<?php

declare(strict_types=1);

namespace Avax\Auth\System\Flows\Login;

use Avax\Auth\System\Capabilities\Identity\IdentityInterface;
use Avax\Auth\System\Capabilities\User\User;
use Avax\Auth\System\Capabilities\UserSource\UserSourceInterface;
use Avax\Auth\System\Capabilities\PasswordHashing\PasswordHasher;
use Avax\Auth\System\Flows\Login\RateLimit\LoginRateLimit;
use Exception;

/**
 * High-level orchestrator for the login process.
 *
 * Banal: The main Login file.
 */
final readonly class Login
{
    public function __construct(
        private UserSourceInterface                   $userSource,
        #[\SensitiveParameter] private PasswordHasher $passwordHasher,
        #[\SensitiveParameter] private IdentityInterface $identity,
        private LoginRateLimit|null                   $rateLimit = null
    ) {}

    /**
     * Execute the login flow.
     *
     * @throws Exception
     */
    public function execute(#[\SensitiveParameter] Credentials $credentials) : User
    {
        if ($this->rateLimit !== null) {
            $this->rateLimit->check(identifier: $credentials->identifier);
        }

        $user = $this->userSource->findByCredentials(credentials: $credentials);

        if (
            $user === null
            || ! $this->passwordHasher->verify(password: $credentials->password, hash: $user->getPasswordHash())
            || ! $user->isActive()
        ) {
            if ($this->rateLimit !== null) {
                $this->rateLimit->recordFailed(identifier: $credentials->identifier);
            }

            throw new Exception(message: 'Invalid credentials.', code: 401);
        }

        $this->identity->issue(user: $user);

        if ($this->rateLimit !== null) {
            $this->rateLimit->reset(identifier: $credentials->identifier);
        }

        return $user;
    }
}
