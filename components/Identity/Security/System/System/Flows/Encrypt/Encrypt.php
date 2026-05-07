<?php

declare(strict_types=1);

namespace Avax\Components\Identity\Security\System\System\Flows\Encrypt;

use Avax\Components\Identity\Security\System\System\Capabilities\Encryption\EncryptedPayload;
use Avax\Components\Identity\Security\System\System\Capabilities\Encryption\EncryptionKey;
use Avax\Components\Identity\Security\System\System\Flows\EncryptValue\EncryptValue;

final readonly class Encrypt
{
    public function __construct(private EncryptValue $encryptValue) {}

    public function execute(mixed $value, ?EncryptionKey $encryptionKey = null) : EncryptedPayload
    {
        return $this->encryptValue->execute(value: $value, key: $encryptionKey);
    }
}
