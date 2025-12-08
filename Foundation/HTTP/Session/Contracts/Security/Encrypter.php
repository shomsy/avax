<?php

declare(strict_types=1);

namespace Avax\HTTP\Session\Contracts\Security;

/**
 * Encrypter - Encryption Contract
 *
 * Defines the contract for session value encryption/decryption.
 * Enables flexible crypto implementations (OpenSSL, Sodium, etc.).
 * 
 * @example
 *   $encrypted = $encrypter->encrypt('sensitive-data');
 *   $decrypted = $encrypter->decrypt($encrypted);
 * 
 * @package Avax\HTTP\Session\Contracts
 */
interface Encrypter
{
    /**
     * Encrypt a value.
     *
     * @param mixed $value The value to encrypt.
     *
     * @return string The encrypted value.
     */
    public function encrypt(mixed $value): string;

    /**
     * Decrypt a value.
     *
     * @param string $encrypted The encrypted value.
     *
     * @return mixed The decrypted value.
     * @throws \RuntimeException If decryption fails.
     */
    public function decrypt(string $encrypted): mixed;
}
