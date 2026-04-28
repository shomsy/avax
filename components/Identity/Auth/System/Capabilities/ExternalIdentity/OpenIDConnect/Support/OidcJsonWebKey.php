<?php

declare(strict_types=1);

namespace Avax\Components\Auth\System\Capabilities\ExternalIdentity\OpenIDConnect\Support;

final readonly class OidcJsonWebKey
{
    public function __construct(public string $keyType, public string $keyId, public string $algorithm, public string $use, public string $modulus, public string $exponent) {}
}
