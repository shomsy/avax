<?php

declare(strict_types=1);

namespace Avax\Auth\Adapters;

/**
 * PasswordHasher handles secure password hashing and verification using Argon2id.
 */
final class PasswordHasher
{
    /**
     * Hash a password using Argon2id.
     *
     * @param string $password The plain-text password to hash.
     *
     * @return string The hashed password.
     */
    public function hash(string $password) : string
    {
        return password_hash($password, PASSWORD_ARGON2ID, [
            'memory_cost' => 65536, // 64MB memory
            'time_cost'   => 4,     // 4 iterations
            'threads'     => 2,     // 2 parallel threads
        ]);
    }

    /**
     * Verify if the given password matches the hashed password.
     *
     * @param string $password       The plain-text password.
     * @param string $hashedPassword The hashed password.
     *
     * @return bool True if the password matches, false otherwise.
     */
    public function verify(string $password, string $hashedPassword) : bool
    {
        return password_verify($password, $hashedPassword);
    }

    /**
     * Check if a password hash needs to be rehashed.
     *
     * @param string $hashedPassword The existing hashed password.
     *
     * @return bool True if rehashing is needed, false otherwise.
     */
    public function needsRehash(string $hashedPassword) : bool
    {
        return password_needs_rehash($hashedPassword, PASSWORD_ARGON2ID, [
            'memory_cost' => 65536,
            'time_cost'   => 4,
            'threads'     => 2,
        ]);
    }
}
