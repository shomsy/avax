<?php

declare(strict_types=1);

namespace Avax\Components\Identity\Security\System\System\Capabilities\Encryption;

/**
 * Interface for encryption/decryption operations.
 */
interface EncrypterInterface
{
    /**
     * Encrypt a value and return an EncryptedPayload.
     *
     * @param string|mixed  $value         The value to encrypt
     * @param EncryptionKey $encryptionKey The encryption key to use
     *
     * @return EncryptedPayload The encrypted payload
     */
    public function encrypt(mixed $value, EncryptionKey $encryptionKey) : EncryptedPayload;

    /**
     * Decrypt an EncryptedPayload back to the original value.
     *
     * @param EncryptedPayload $encryptedPayload The encrypted payload to decrypt
     * @param EncryptionKey    $encryptionKey    The encryption key to use
     *
     * @return string The decrypted value
     */
    public function decrypt(EncryptedPayload $encryptedPayload, EncryptionKey $encryptionKey) : string;

    /**
     * Check if this encrypter supports the given cipher.
     *
     * @param string $cipher The cipher name to check
     */
    public function supports(string $cipher) : bool;
}
