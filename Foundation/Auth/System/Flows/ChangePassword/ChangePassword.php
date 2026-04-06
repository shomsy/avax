<?php

declare(strict_types=1);

namespace Avax\Auth\System\Flows\ChangePassword;

use Avax\Auth\System\Capabilities\User\User;
use Avax\Auth\System\Capabilities\UserSource\UserSourceInterface;
use Avax\Auth\System\Capabilities\PasswordHashing\PasswordHasher;

/**
 * High-level orchestrator for the password change process.
 * 
 * Banal: The file that changes passwords.
 */
final readonly class ChangePassword
{
    public function __construct(
        private UserSourceInterface $userSource,
        #[\SensitiveParameter] private PasswordHasher $passwordHasher
    ) {}

    /**
     * @throws \Exception
     */
    public function execute(User $user, ChangePasswordData $data) : void
    {
        if (! $this->passwordHasher->verify(password: $data->currentPassword, hash: $user->getPasswordHash())) {
            throw new \Exception(message: 'Current password is incorrect.', code: 403);
        }

        $newHash = $this->passwordHasher->hash(password: $data->newPassword);
        
        $this->userSource->updatePassword(id: $user->getId(), passwordHash: $newHash);
    }
}
