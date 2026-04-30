<?php

declare(strict_types=1);

namespace Avax\Components\Identity\Auth\System\Capabilities\Identity\PasswordHashing;

use SensitiveParameter;

/**
 * Standard utility for password hashing and verification within the Auth System.
 */
final readonly class PasswordHasher
{
    /**
     * @param array<string, int|string|bool> $options
     */
    private string $algo;

    /** @var array<string, int|string|bool> */
    private array $options;

    /**
     * @param array<string, int|string|bool>|null $options
     */
    public function __construct(
        string|null $algo = null,
        array  $options = null,
    )
    {
        $this->algo = $algo
            ?? $this->inferAlgorithmFromOptions(options: $options)
            ?? $this->defaultAlgorithm();
        $this->options = $options ?? $this->defaultOptions(algo: $this->algo);
    }

    /**
     * @param array<string, int|string|bool>|null $options
     */
    private function inferAlgorithmFromOptions(array|null $options) : string|null
    {
        if ($options === null) {
            return null;
        }

        if (array_key_exists(key: 'cost', array: $options)) {
            return PASSWORD_BCRYPT;
        }

        if (
            defined(constant_name: 'PASSWORD_ARGON2ID')
            && (
                array_key_exists(key: 'memory_cost', array: $options)
                || array_key_exists(key: 'time_cost', array: $options)
                || array_key_exists(key: 'threads', array: $options)
            )
        ) {
            return PASSWORD_ARGON2ID;
        }

        return null;
    }

    private function defaultAlgorithm() : string
    {
        return defined(constant_name: 'PASSWORD_ARGON2ID') ? PASSWORD_ARGON2ID : PASSWORD_DEFAULT;
    }

    /**
     * @return array<string, int|string|bool>
     */
    private function defaultOptions(string $algo) : array
    {
        if (defined(constant_name: 'PASSWORD_ARGON2ID') && $algo === PASSWORD_ARGON2ID) {
            return [
                'memory_cost' => PASSWORD_ARGON2_DEFAULT_MEMORY_COST,
                'time_cost'   => PASSWORD_ARGON2_DEFAULT_TIME_COST,
                'threads'     => PASSWORD_ARGON2_DEFAULT_THREADS,
            ];
        }

        return ['cost' => 12];
    }

    public function hash(#[SensitiveParameter] string $password) : string
    {
        return password_hash(password: $password, algo: $this->algo, options: $this->options);
    }

    public function verify(#[SensitiveParameter] string $password, #[SensitiveParameter] string $hash) : bool
    {
        return password_verify(password: $password, hash: $hash);
    }

    /**
     * Generates a dummy hash for timing attack mitigation.
     */
    public function dummyHash() : string
    {
        // Use a fixed cost dummy hash that looks real.
        // This hash is for the password 'password' with cost 12.
        return '$2y$12$nO.MMTy.SQpyLSIsZpXOnuSnt.SQpyLSIsZpXOnuSnt.SQpyLSi';
    }

    public function needsRehash(#[SensitiveParameter] string $hash) : bool
    {
        return password_needs_rehash(hash: $hash, algo: $this->algo, options: $this->options);
    }
}
