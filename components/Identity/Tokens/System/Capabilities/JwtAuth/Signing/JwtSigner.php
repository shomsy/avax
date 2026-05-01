<?php

declare(strict_types=1);

namespace Avax\Components\Identity\Tokens\System\Capabilities\JwtAuth\System\Capabilities\Signing;

use Firebase\JWT\JWT;

final class JwtSigner
{
    private string $secret;
    private string $algo;

    public function __construct(string $secret, string $algo = 'HS256')
    {
        $this->secret = $secret;
        $this->algo   = $algo;
    }

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