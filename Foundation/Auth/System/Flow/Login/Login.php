<?php

declare(strict_types=1);

namespace Avax\Auth\System\Flow\Login;

use Avax\Auth\System\Capability\Identity\IdentityInterface;
use Avax\Auth\System\Capability\User\User;
use Avax\Auth\System\Capability\UserSource\UserSourceInterface;
use Avax\Auth\System\Capability\PasswordHashing\PasswordHasher;
use Avax\Auth\System\Flow\Login\RateLimit\LoginRateLimit;
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
        $this->rateLimit?->check(identifier: $credentials->identifier);

        $user = $this->userSource->findByCredentials(credentials: $credentials);

        if (
            $user === null
            || ! $this->passwordHasher->verify(password: $credentials->password, hash: $user->getPasswordHash())
            || ! $user->isActive()
        ) {
            $this->rateLimit?->recordFailed(identifier: $credentials->identifier);

            throw new Exception(message: 'Invalid credentials.', code: 401);
        }

        $this->identity->issue(user: $user);

        $this->rateLimit?->reset(identifier: $credentials->identifier);

        return $user;
    }
}
