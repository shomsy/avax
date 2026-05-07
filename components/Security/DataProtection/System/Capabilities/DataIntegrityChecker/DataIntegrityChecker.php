<?php

declare(strict_types=1);

namespace Avax\Components\Security\DataProtection\System\Capabilities\DataIntegrityChecker;

final class DataIntegrityChecker
{
    public function verifyHmac(string $data, string $key, string $expectedHmac) : bool
    {
        $computed = $this->computeHmac(data: $data, key: $key);

        return hash_equals(known_string: $computed, user_string: $expectedHmac);
    }

    public function computeHmac(string $data, string $key) : string
    {
        return hash_hmac(algo: 'sha256', data: $data, key: $key);
    }
}
