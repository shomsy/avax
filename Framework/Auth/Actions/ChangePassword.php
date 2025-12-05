<?php

declare(strict_types=1);

namespace Gemini\Auth\Actions;

use Gemini\Auth\Contracts\UserSourceInterface;
use Gemini\Auth\Contracts\UserInterface;
use Gemini\Auth\Adapters\PasswordHasher;

final readonly class ChangePassword
{
    public function __construct(
        private UserSourceInterface $userProvider,
        private PasswordHasher $passwordHasher
    ) {}

    public function execute(UserInterface $user, string $currentPassword, string $newPassword): void
    {
        if (!$this->passwordHasher->verify($currentPassword, $user->getPassword())) {
            throw new \RuntimeException('Current password is incorrect.');
        }

        $hashed = $this->passwordHasher->hash($newPassword);
        $this->userProvider->updatePassword($user->getId(), $hashed);
    }
}
