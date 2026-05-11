<?php

declare(strict_types=1);

namespace Avax\Components\Security\Cryptography\System\Flows\EncryptValue;

use Avax\Components\Security\Cryptography\System\Capabilities\Encryption\EncryptedPayload;
use Avax\Components\Security\Cryptography\System\Capabilities\Encryption\EncrypterInterface;
use Avax\Components\Security\Cryptography\System\Capabilities\Encryption\EncryptionKey;
use Avax\Components\Security\Cryptography\System\Capabilities\Encryption\KeyResolver;

/**
 * Flow for encrypting a value using the current encryption key.
 */
final readonly class EncryptValue
{
    public function __construct(
        private EncrypterInterface $encrypter,
        private KeyResolver        $keyResolver,
    ) {}

    /**
     * Execute the encryption flow.
     *
     * @param mixed              $value The value to encrypt
     * @param EncryptionKey|null $key   Optional specific key to use (defaults to current key)
     *
     * @return EncryptedPayload The encrypted payload
     */
    public function execute(mixed $value, EncryptionKey|null $key = null) : EncryptedPayload
    {
        $encryptionKey = $key ?? $this->keyResolver->getCurrentKey();

        return $this->encrypter->encrypt($value, $encryptionKey);
    }
}
