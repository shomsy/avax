<?php

declare(strict_types=1);

namespace Avax\Components\Security\DataProtection\System\Flows\DecryptData;

use Avax\Components\Security\DataProtection\System\Capabilities\EncryptionService\EncryptionService;
use Avax\Components\Security\DataProtection\System\Capabilities\KeyManager\KeyManager;
use RuntimeException;

final readonly class DecryptData
{
    public function __construct(
        private EncryptionService $encryption = new EncryptionService(),
        private KeyManager        $keyManager = new KeyManager(),
    ) {}

    /**
     * @param array{ciphertext:string,iv:string,tag?:string} $encrypted
     */
    public function execute(array $encrypted, string $keyId) : string
    {
        $key = $this->keyManager->getKey(keyId: $keyId);

        if ($key === null) {
            throw new RuntimeException(message: "Key not found: {$keyId}");
        }

        return $this->encryption->decrypt(
            ciphertext: $encrypted['ciphertext'],
            key       : $key,
            iv        : $encrypted['iv'],
            tag       : $encrypted['tag'] ?? null,
        );
    }
}
