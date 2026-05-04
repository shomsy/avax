<?php

declare(strict_types=1);

namespace Avax\Components\Identity\Tokens\System\Capabilities\JwtAuth\Tokens;

final readonly class AccessToken
{
    public function __construct(
        public string $sub,
        public array  $scopes,
        public int    $exp,
        public int    $iat,
        public string $jti,
    )
    {
    }

    public static function fromPayload(array $payload): self
    {
        return new self(
            sub: $payload['sub'],
            scopes: $payload['scopes'] ?? [],
            exp: $payload['exp'],
            iat: $payload['iat'],
            jti: $payload['jti'] ?? '',
        );
    }

    public function toPayload(): array
    {
        return [
            'sub' => $this->sub,
            'scopes' => $this->scopes,
            'exp' => $this->exp,
            'iat' => $this->iat,
            'jti' => $this->jti,
            'type' => 'access',
        ];
    }
}
