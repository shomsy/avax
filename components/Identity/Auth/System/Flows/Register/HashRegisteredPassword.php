<?php

declare(strict_types=1);

namespace Avax\Components\Identity\Auth\System\Flows\Register;

use Avax\Components\Security\System\Hashing\System\Capabilities\PasswordHashing\PasswordHasher;

/**
 * HashRegisteredPassword - Action to hash password for registration.
 * 1:1 alignment with refactor.md.
 */
final readonly class HashRegisteredPassword
{
    public function __construct(
        private PasswordHasher $hasher,
    ) {}

    public function execute(string $password) : string
    {
        return $this->hasher->hash($password);
    }
}
