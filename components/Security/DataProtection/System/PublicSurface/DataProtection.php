<?php

declare(strict_types=1);

namespace Avax\Components\Security\DataProtection\System\PublicSurface;

use Avax\Components\Security\DataProtection\System\Capabilities\DataIntegrityChecker\DataIntegrityChecker;
use Avax\Components\Security\DataProtection\System\Capabilities\EncryptionService\EncryptionService;
use Avax\Components\Security\DataProtection\System\Capabilities\KeyManager\KeyManager;
use Avax\Components\Security\DataProtection\System\Flows\DecryptData\DecryptData;
use Avax\Components\Security\DataProtection\System\Flows\EncryptData\EncryptData;
use Avax\Components\Security\DataProtection\System\Flows\RotateEncryptionKey\RotateEncryptionKey;

final class DataProtection
{
    public static function keyManager() : KeyManager
    {
        return new KeyManager();
    }

    public static function encryptionService(string $cipher = 'aes-256-gcm') : EncryptionService
    {
        return new EncryptionService(cipher: $cipher);
    }

    public static function integrityChecker() : DataIntegrityChecker
    {
        return new DataIntegrityChecker();
    }

    /**
     * @return array{encrypted:array{ciphertext:string,iv:string,tag?:string},key_id:string}
     */
    public static function encrypt(string $data, string $keyId = 'default') : array
    {
        return (new EncryptData())->execute(data: $data, keyId: $keyId);
    }

    /**
     * @param array{ciphertext:string,iv:string,tag?:string} $encrypted
     */
    public static function decrypt(array $encrypted, string $keyId) : string
    {
        return (new DecryptData())->execute(encrypted: $encrypted, keyId: $keyId);
    }

    /**
     * @return array<string, string>
     */
    public static function rotateKey(string $oldKeyId, string $newKeyId) : array
    {
        return (new RotateEncryptionKey())->execute(oldKeyId: $oldKeyId, newKeyId: $newKeyId);
    }

    public static function computeHmac(string $data, string $key) : string
    {
        return (new DataIntegrityChecker())->computeHmac(data: $data, key: $key);
    }

    public static function verifyHmac(string $data, string $key, string $expectedHmac) : bool
    {
        return (new DataIntegrityChecker())->verifyHmac(data: $data, key: $key, expectedHmac: $expectedHmac);
    }
}
