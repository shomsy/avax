<?php

declare(strict_types=1);

namespace Avax\Components\Identity\Tokens\System\Capabilities\JwtAuth\Tokens;

final readonly class JwtTokens
{
    public function __construct(
        public string $sub,
        public array  $scopes,
        public int    $exp,
        public int    $iat,
        public string $jti,
    ) {}

    public static function fromPayload(array $payload) : self
    {
        return new self(
            sub   : $payload['sub'],
            scopes: $payload['scopes'] ?? [],
            exp   : $payload['exp'],
            iat   : $payload['iat'],
            jti   : $payload['jti'] ?? '',
        );
    }

    public function toPayload() : array
    {
        return [
            'sub'    => $this->sub,
            'scopes' => $this->scopes,
            'exp'    => $this->exp,
            'iat'    => $this->iat,
            'jti'    => $this->jti,
            'type'   => 'access',
        ];
    }
}

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

final readonly class TokenPair
{
    public function __construct(
        public string $accessToken,
        public string $refreshToken,
        public int    $expiresIn,
        public string $tokenType,
    ) {}

    public function toArray() : array
    {
        return [
            'access_token'  => $this->accessToken,
            'refresh_token' => $this->refreshToken,
            'expires_in'    => $this->expiresIn,
            'token_type'    => $this->tokenType,
        ];
    }
}
