<?php

declare(strict_types=1);

namespace Avax\Auth\Actions;

use Avax\Auth\Contracts\UserSourceInterface;
use Avax\Auth\Contracts\UserInterface;
use Avax\Auth\Adapters\PasswordHasher;
use SensitiveParameter;

final readonly class ChangePassword
{
    public function __construct(
        private UserSourceInterface $userProvider,
        #[SensitiveParameter] private PasswordHasher $passwordHasher
    ){}

    public function execute(UserInterface $user, #[\SensitiveParameter] string $currentPassword, #[\SensitiveParameter] string $newPassword): void
    {
        if (!$this->passwordHasher->verify(password: $currentPassword, hashedPassword: $user->getPassword())) {
            throw new \RuntimeException(message: 'Current password is incorrect.');
        }

        $hashed = $this->passwordHasher->hash(password: $newPassword);
        $this->userProvider->updatePassword($user->getId(), $hashed);
    }
}
