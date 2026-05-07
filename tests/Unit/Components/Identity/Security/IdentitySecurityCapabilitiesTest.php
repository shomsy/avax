<?php

declare(strict_types=1);

namespace Avax\Tests\Unit\Components\Identity\Security;

use Avax\Components\Identity\Security\System\Capabilities\Encryption\AesEncrypter;
use Avax\Components\Identity\Security\System\Capabilities\Encryption\EncryptionKey;
use PHPUnit\Framework\TestCase;

final class IdentitySecurityCapabilitiesTest extends TestCase
{
    public function test_aes_encrypter_roundtrip() : void
    {
        $keyMaterial   = str_repeat('a', 32);
        $encryptionKey = new EncryptionKey($keyMaterial);
        $encrypter     = new AesEncrypter($keyMaterial);

        $encrypted = $encrypter->encrypt('secret-data', $encryptionKey);
        $decrypted = $encrypter->decrypt($encrypted, $encryptionKey);

        $this->assertSame('secret-data', $decrypted);
    }
}
