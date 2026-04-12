<?php

declare(strict_types=1);

namespace Avax\Auth\System\Capability\PasswordHashing;

use SensitiveParameter;

/**
 * Standard utility for password hashing and verification within the Auth System.
 */
final class PasswordHasher
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
        array|null  $options = null
    )
    {
        $this->algo    = $algo
            ?? $this->inferAlgorithmFromOptions($options)
            ?? $this->defaultAlgorithm();
        $this->options = $options ?? $this->defaultOptions($this->algo);
    }

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

    private function defaultAlgorithm() : string
    {
        return defined('PASSWORD_ARGON2ID') ? PASSWORD_ARGON2ID : PASSWORD_DEFAULT;
    }

    /**
     * @param array<string, int|string|bool>|null $options
     */
    private function inferAlgorithmFromOptions(array|null $options) : string|null
    {
        if ($options === null) {
            return null;
        }

        if (array_key_exists('cost', $options)) {
            return PASSWORD_BCRYPT;
        }

        if (
            defined('PASSWORD_ARGON2ID')
            && (
                array_key_exists('memory_cost', $options)
                || array_key_exists('time_cost', $options)
                || array_key_exists('threads', $options)
            )
        ) {
            return PASSWORD_ARGON2ID;
        }

        return null;
    }

    /**
     * @return array<string, int|string|bool>
     */
    private function defaultOptions(string $algo) : array
    {
        if (defined('PASSWORD_ARGON2ID') && $algo === PASSWORD_ARGON2ID) {
            return [
                'memory_cost' => PASSWORD_ARGON2_DEFAULT_MEMORY_COST,
                'time_cost'   => PASSWORD_ARGON2_DEFAULT_TIME_COST,
                'threads'     => PASSWORD_ARGON2_DEFAULT_THREADS,
            ];
        }

        return ['cost' => 12];
    }
}
