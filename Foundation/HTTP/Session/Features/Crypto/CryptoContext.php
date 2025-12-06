<?php

declare(strict_types=1);

namespace Avax\HTTP\Session\Features\Crypto;

/**
 * CryptoContext
 *
 * Value object for crypto configuration.
 *
 * @package Avax\HTTP\Session\Features\Crypto
 */
final readonly class CryptoContext
{
    /**
     * CryptoContext Constructor.
     *
     * @param string $key       The encryption key.
     * @param string $algorithm The encryption algorithm.
     * @param string $cipher    The cipher method.
     */
    public function __construct(
        public string $key,
        public string $algorithm = 'aes-256-gcm',
        public string $cipher = 'AES-256-GCM'
    ) {}

    /**
     * Create from key.
     *
     * @param string $key The encryption key.
     *
     * @return self
     */
    public static function fromKey(string $key): self
    {
        return new self($key);
    }

    /**
     * Create with algorithm.
     *
     * @param string $key       The encryption key.
     * @param string $algorithm The algorithm.
     *
     * @return self
     */
    public static function withAlgorithm(string $key, string $algorithm): self
    {
        return new self($key, $algorithm, strtoupper($algorithm));
    }
}
