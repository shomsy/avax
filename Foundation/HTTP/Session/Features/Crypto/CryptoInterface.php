<?php

declare(strict_types=1);

namespace Avax\HTTP\Session\Features\Crypto;

/**
 * CryptoInterface
 *
 * Contract for session encryption adapters.
 *
 * @package Avax\HTTP\Session\Features\Crypto
 */
interface CryptoInterface
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
     */
    public function decrypt(string $encrypted): mixed;

    /**
     * Get algorithm name.
     *
     * @return string
     */
    public function getAlgorithm(): string;
}
