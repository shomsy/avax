<?php

declare(strict_types=1);

namespace Avax\Components\Identity\Security\System\Flows\DecryptValue;

use Avax\Components\Identity\Security\System\Capabilities\Encryption\EncryptedPayload;
use Avax\Components\Identity\Security\System\Capabilities\Encryption\EncrypterInterface;
use Avax\Components\Identity\Security\System\Capabilities\Encryption\KeyResolver;
use Avax\Components\Identity\Security\System\Foundation\Failure\DecryptionFailed;

use function is_string;

/**
 * Flow for decrypting an encrypted payload.
 *
 * Automatically resolves the correct key based on the payload's key version.
 */
final readonly class DecryptValue
{
    public function __construct(
        private EncrypterInterface $encrypter,
        private KeyResolver $keyResolver,
    ) {}

    /**
     * Execute the decryption flow.
     *
     * @param  EncryptedPayload|string  $payload  The encrypted payload or serialized string
     * @return string The decrypted plaintext
     *
     * @throws DecryptionFailed if decryption fails or payload has been tampered with
     */
    public function execute(EncryptedPayload|string $payload): string
    {
        if (is_string($payload)) {
            $payload = EncryptedPayload::deserialize($payload);
        }

        $key = $this->keyResolver->getKeyByVersion($payload->keyVersion());

        if ($key === null) {
            throw new DecryptionFailed(
                sprintf('No encryption key found for version "%s"', $payload->keyVersion()),
            );
        }

        return $this->encrypter->decrypt($payload, $key);
    }
}
