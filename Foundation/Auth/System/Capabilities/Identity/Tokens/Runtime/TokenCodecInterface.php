<?php

declare(strict_types=1);

namespace Avax\Auth\System\Capabilities\Identity\Tokens\Runtime;

/**
 * Signs and verifies opaque token strings from package-owned claims.
 */
interface TokenCodecInterface
{
    /**
     * @param array<string, scalar|array<array-key, scalar>> $claims
     */
    public function encode(array $claims) : string;

    /**
     * @return array<string, mixed>|null
     */
    public function decode(string $token) : array|null;
}
