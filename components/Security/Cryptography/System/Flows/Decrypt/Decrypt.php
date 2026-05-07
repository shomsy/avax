<?php

declare(strict_types=1);

namespace Avax\Components\Security\Cryptography\System\Flows\Decrypt;

use Avax\Components\Security\Cryptography\System\Capabilities\Encryption\EncryptedPayload;
use Avax\Components\Security\Cryptography\System\Flows\DecryptValue\DecryptValue;

final readonly class Decrypt
{
    public function __construct(private DecryptValue $decryptValue) {}

    public function execute(EncryptedPayload|string $payload) : string
    {
        return $this->decryptValue->execute(payload: $payload);
    }
}
