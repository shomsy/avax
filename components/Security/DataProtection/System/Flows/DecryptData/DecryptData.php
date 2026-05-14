<?php

declare(strict_types=1);

namespace Avax\Components\Security\DataProtection\System\Flows\DecryptData;

use Avax\Components\Security\DataProtection\System\Capabilities\EncryptDataPayload\EncryptDataPayload;
use Avax\Components\Security\DataProtection\System\Capabilities\ManageKeys\ManageKeys;
use RuntimeException;

final readonly class DecryptData
{
    public function __construct(
        private EncryptDataPayload $encryption = new EncryptDataPayload(),
        private ManageKeys        $keyManager = new ManageKeys(),
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
