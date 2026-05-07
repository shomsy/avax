<?php

declare(strict_types=1);

namespace Avax\Components\Identity\Auth\System\System\Flows\Login;

use Avax\Components\Identity\Auth\System\System\Capabilities\Identity\User\User;
use Avax\Components\Security\Hashing\System\Capabilities\PasswordHashing\PasswordHasher;

/**
 * VerifyPassword - Action to verify user password against hash.
 * 1:1 alignment with refactor.md.
 */
final readonly class VerifyPassword
{
    public function __construct(
        private PasswordHasher $passwordHasher,
    ) {}

    public function execute(User $user, string $password) : bool
    {
        return $this->passwordHasher->verify($password, $user->passwordHash);
    }
}
