<?php

declare(strict_types=1);

namespace Avax\Auth\System\Capability\Oidc;

final readonly class OidcJsonWebKey
{
    public string $exponent;
    public string $modulus;
    public string $use;
    public string $algorithm;
    public string $keyId;
    public string $keyType;

    public function __construct(
        string $keyType,
        string $keyId,
        string $algorithm,
        string $use,
        string $modulus,
        string $exponent
    )
    {
        $this->keyType   = $keyType;
        $this->keyId     = $keyId;
        $this->algorithm = $algorithm;
        $this->use       = $use;
        $this->modulus   = $modulus;
        $this->exponent  = $exponent;
    }
}
