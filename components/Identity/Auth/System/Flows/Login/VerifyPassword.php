<?php

declare(strict_types=1);

namespace Avax\Components\Identity\Auth\System\Flows\Login;

use Avax\Components\Identity\Auth\System\Capabilities\Identity\User\User;
use Avax\Components\Security\Hashing\System\Capabilities\PasswordHashing\PasswordHasher;

/**
 * VerifyPassword - Action to verify user password against hash.
 * 1:1 alignment with refactor.md.
 */
final readonly class VerifyPassword
{
    public function __construct(
        private PasswordHasher $hasher,
    ) {}

    public function execute(User $user, string $password) : bool
    {
        return $this->hasher->verify($password, $user->passwordHash);
    }
}
