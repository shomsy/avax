<?php

declare(strict_types=1);

namespace Avax\Components\Identity\Tokens\System\System\Capabilities\JwtAuth\Tokens;

final readonly class RefreshToken
{
    public function __construct(
        public string $sub,
        public string $jti,
        public int    $exp,
        public int    $iat,
    ) {}

    public static function fromPayload(array $payload) : self
    {
        return new self(
            sub: $payload['sub'],
            jti: $payload['jti'],
            exp: $payload['exp'],
            iat: $payload['iat'],
        );
    }
}
