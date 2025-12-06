<?php

declare(strict_types=1);

namespace Avax\HTTP\Session\Features\Crypto\Adapters;

use Avax\HTTP\Session\Features\Crypto\CryptoInterface;
use Avax\Security\Encryption\Contracts\EncrypterInterface;

/**
 * OpenSSLAdapter
 *
 * OpenSSL-based encryption adapter.
 *
 * @package Avax\HTTP\Session\Features\Crypto\Adapters
 */
final readonly class OpenSSLAdapter implements CryptoInterface
{
    /**
     * OpenSSLAdapter Constructor.
     *
     * @param EncrypterInterface $encrypter The encrypter instance.
     */
    public function __construct(
        private EncrypterInterface $encrypter
    ) {}

    /**
     * {@inheritdoc}
     */
    public function encrypt(mixed $value): string
    {
        return $this->encrypter->encrypt(serialize($value));
    }

    /**
     * {@inheritdoc}
     */
    public function decrypt(string $encrypted): mixed
    {
        $decrypted = $this->encrypter->decrypt($encrypted);
        return unserialize($decrypted);
    }

    /**
     * {@inheritdoc}
     */
    public function getAlgorithm(): string
    {
        return 'openssl';
    }
}
