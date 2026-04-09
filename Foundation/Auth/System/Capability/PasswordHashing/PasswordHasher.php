<?php

declare(strict_types=1);

namespace Avax\Auth\System\Capability\PasswordHashing;

use SensitiveParameter;

/**
 * Standard utility for password hashing and verification within the Auth System.
 */
final readonly class PasswordHasher
{
    /**
     * @param array<string, int|string|bool> $options
     */
    public function __construct(
        private string $algo = PASSWORD_DEFAULT,
        private array  $options = ['cost' => 12]
    ) {}

    public function hash(#[SensitiveParameter] string $password) : string
    {
        return password_hash(password: $password, algo: $this->algo, options: $this->options);
    }

    public function verify(#[SensitiveParameter] string $password, #[SensitiveParameter] string $hash) : bool
    {
        return password_verify(password: $password, hash: $hash);
    }

    public function needsRehash(#[SensitiveParameter] string $hash) : bool
    {
        return password_needs_rehash(hash: $hash, algo: $this->algo, options: $this->options);
    }
}
