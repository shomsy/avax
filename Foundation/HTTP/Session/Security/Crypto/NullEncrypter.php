<?php

declare(strict_types=1);

namespace Avax\HTTP\Session\Security\Crypto;

use Avax\HTTP\Session\Contracts\Security\Encrypter;

/**
 * NullEncrypter - No-Op Encrypter
 *
 * Dummy encrypter for testing or development.
 * Does NOT actually encrypt - just base64 encodes.
 *
 * @warning DO NOT use in production!
 *
 * @package Avax\HTTP\Session\Crypto
 */
final class NullEncrypter implements Encrypter
{
    /**
     * {@inheritdoc}
     */
    public function encrypt(mixed $value) : string
    {
        return base64_encode(serialize($value));
    }

    /**
     * {@inheritdoc}
     */
    public function decrypt(string $encrypted) : mixed
    {
        return unserialize(base64_decode($encrypted));
    }
}
