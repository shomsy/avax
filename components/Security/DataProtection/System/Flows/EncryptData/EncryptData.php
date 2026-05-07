<?php

declare(strict_types=1);

namespace Avax\Components\Security\DataProtection\System\Flows\EncryptData;

use Avax\Components\Security\DataProtection\System\Capabilities\EncryptionService\EncryptionService;
use Avax\Components\Security\DataProtection\System\Capabilities\KeyManager\KeyManager;

final readonly class EncryptData
{
    public function __construct(
        private EncryptionService $encryption = new EncryptionService(),
        private KeyManager        $keyManager = new KeyManager(),
    ) {}

    /**
     * @return array{encrypted:array{ciphertext:string,iv:string,tag?:string},key_id:string}
     */
    public function execute(string $data, string $keyId = 'default') : array
    {
        $key = $this->keyManager->getKey(keyId: $keyId);

        if ($key === null) {
            $key = $this->keyManager->generateKey(keyId: $keyId);
        }

        $encrypted = $this->encryption->encrypt(data: $data, key: $key);

        return [
            'encrypted' => $encrypted,
            'key_id'    => $keyId,
        ];
    }
}
