<?php

declare(strict_types=1);

namespace Avax\Components\Identity\Tokens\System\System\Capabilities\JwtAuth\Tokens;

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
