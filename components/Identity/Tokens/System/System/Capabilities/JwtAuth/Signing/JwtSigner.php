<?php

declare(strict_types=1);

namespace Avax\Components\Identity\Tokens\System\System\Capabilities\JwtAuth\Signing;

use Firebase\JWT\JWT;

final readonly class JwtSigner
{
    public function __construct(private string $secret, private string $algo = 'HS256') {}

    public function sign(array $payload) : string
    {
        return JWT::encode($payload, $this->secret, $this->algo);
    }

    public function getSecret() : string
    {
        return $this->secret;
    }

    public function getAlgo() : string
    {
        return $this->algo;
    }
}
