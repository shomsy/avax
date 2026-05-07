<?php

declare(strict_types=1);

namespace Avax\Components\Security\DataProtection\System\Flows\RotateEncryptionKey;

use Avax\Components\Security\DataProtection\System\Capabilities\KeyManager\KeyManager;

final readonly class RotateEncryptionKey
{
    public function __construct(
        private KeyManager $keyManager = new KeyManager(),
    ) {}

    /**
     * @return array{old_key_id:string,new_key_id:string,active_key_id:string}
     */
    public function execute(string $oldKeyId, string $newKeyId) : array
    {
        $newKey = $this->keyManager->rotateKey(oldKeyId: $oldKeyId, newKeyId: $newKeyId);

        return [
            'old_key_id'    => $oldKeyId,
            'new_key_id'    => $newKeyId,
            'active_key_id' => $this->keyManager->activeKeyId(),
        ];
    }
}
