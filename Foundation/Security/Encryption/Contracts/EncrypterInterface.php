<?php

declare(strict_types=1);

namespace Avax\Security\Encryption\Contracts;

/**
 * Interface EncrypterInterface
 *
 * Defines the contract for encryption services.
 */
interface EncrypterInterface
{
    /**
     * Encrypt the given value.
     *
     * @param  mixed  $value  The value to encrypt.
     * @return string The encrypted string.
     */
    public function encrypt(mixed $value): string;

    /**
     * Decrypt the given payload.
     *
     * @param  string  $payload  The encrypted payload.
     * @return mixed The decrypted value.
     */
    public function decrypt(string $payload): mixed;
}
