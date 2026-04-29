<?php
declare(strict_types=1);

namespace Avax\Components\Identity\Auth\System\Capabilities\PasswordHashing;

/**
 * PasswordHasher - Implementation of Bcrypt/Argon2 hashing.
 * 1:1 alignment with refactor.md.
 */
final readonly class PasswordHasher implements PasswordHasherInterface
{
    public function hash(string $password) : string
    {
        return password_hash($password, PASSWORD_DEFAULT);
    }

    public function verify(string $password, string $hash) : bool
    {
        return password_verify($password, $hash);
    }

    public function needsRehash(string $hash) : bool
    {
        return password_needs_rehash($hash, PASSWORD_DEFAULT);
    }
}
