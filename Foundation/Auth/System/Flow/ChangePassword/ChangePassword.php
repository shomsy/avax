<?php

declare(strict_types=1);

namespace Avax\Auth\System\Flow\ChangePassword;

use Avax\Auth\System\Capability\User\User;
use Avax\Auth\System\Capability\UserSource\UserSourceInterface;
use Avax\Auth\System\Capability\PasswordHashing\PasswordHasher;
use Avax\Auth\System\Flow\Login\RateLimit\LoginRateLimit;

/**
 * High-level orchestrator for the password change process.
 * 
 * Banal: The file that changes passwords.
 */
final readonly class ChangePassword
{
    public function __construct(
        private UserSourceInterface $userSource,
        #[\SensitiveParameter] private PasswordHasher $passwordHasher,
        private LoginRateLimit|null $rateLimit = null
    ) {}

    /**
     * @throws \Exception
     */
    public function execute(User $user, ChangePasswordData $data) : void
    {
        $this->rateLimit?->check(identifier: (string) $user->getId());

        if (! $this->passwordHasher->verify(password: $data->currentPassword, hash: $user->getPasswordHash())) {
            $this->rateLimit?->recordFailed(identifier: (string) $user->getId());

            throw new \Exception(message: 'Current password is incorrect.', code: 403);
        }

        $newHash = $this->passwordHasher->hash(password: $data->newPassword);
        
        $this->userSource->updatePassword(id: $user->getId(), passwordHash: $newHash);

        $this->rateLimit?->reset(identifier: (string) $user->getId());
    }
}
