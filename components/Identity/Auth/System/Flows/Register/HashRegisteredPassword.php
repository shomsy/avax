<?php

declare(strict_types=1);

namespace Avax\Components\Identity\Auth\System\Flows\Register;

use Avax\Components\Security\Hashing\System\Capabilities\PasswordHashing\PasswordHasher;

/**
 * HashRegisteredPassword - Action to hash password for registration.
 * 1:1 alignment with refactor.md.
 */
final readonly class HashRegisteredPassword
{
    public function __construct(
        private PasswordHasher $passwordHasher,
    ) {
    }

    public function execute(string $password): string
    {
        return $this->passwordHasher->hash($password);
    }
}
