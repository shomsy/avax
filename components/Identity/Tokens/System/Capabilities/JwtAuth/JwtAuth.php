<?php

declare(strict_types=1);

namespace Avax\Components\Identity\Tokens\System\Capabilities\JwtAuth;

use Avax\Components\Identity\Tokens\System\Capabilities\JwtAuth\Tokens\AccessToken;
use Avax\Components\Identity\Tokens\System\Capabilities\JwtAuth\Tokens\TokenPair;
use Avax\Components\Identity\Tokens\System\Capabilities\JwtAuth\Signing\JwtSigner;
use Avax\Components\Identity\Tokens\System\Capabilities\JwtAuth\Verification\TokenVerifier;
use Avax\Components\Identity\Tokens\System\Foundation\Time\Clock;
use RuntimeException;
use Throwable;

final readonly class JwtAuth
{
    public function __construct(
        private JwtSigner      $jwtSigner,
        private TokenVerifier  $tokenVerifier,
        private TokenBlacklist $tokenBlacklist,
        private Clock          $clock,
    ) {}

    public function verify(string $token) : AccessToken
    {
        if ($this->tokenBlacklist->isRevoked($token)) {
            throw new RuntimeException('Token has been revoked');
        }

        return $this->tokenVerifier->verify($token);
    }

    public function refresh(string $refreshToken) : TokenPair
    {
        $payload = $this->tokenVerifier->verifyPayload($refreshToken);

        if (($payload['type'] ?? '') !== 'refresh') {
            throw new RuntimeException('Invalid token type for refresh');
        }

        if (($payload['exp'] ?? 0) < $this->clock->now()) {
            throw new RuntimeException('Refresh token has expired');
        }

        $this->tokenBlacklist->revoke($refreshToken);

        $user = ['id' => $payload['sub']];

        return $this->issue($user, $payload['scopes'] ?? []);
    }

    public function revoke(string $token) : void
    {
        $this->tokenBlacklist->revoke($token);
    }

    public function issue(array $user, array $scopes = []) : TokenPair
    {
        $now            = $this->clock->now();
        $accessExpires  = $now + 900;
        $refreshExpires = $now + 604800;

        $accessPayload = [
            'sub'    => $user['id'],
            'scopes' => $scopes,
            'exp'    => $accessExpires,
            'iat'    => $now,
            'type'   => 'access',
        ];

        $refreshPayload = [
            'scopes' => $scopes,
            'sub'    => $user['id'],
            'jti'    => bin2hex(random_bytes(16)),
            'exp'    => $refreshExpires,
            'iat'    => $now,
            'type'   => 'refresh',
        ];

        $accessToken  = $this->jwtSigner->sign($accessPayload);
        $refreshToken = $this->jwtSigner->sign($refreshPayload);

        return new TokenPair(
            accessToken : $accessToken,
            refreshToken: $refreshToken,
            expiresIn   : 900,
            tokenType   : 'Bearer',
        );
    }

    public function introspect(string $token) : array
    {
        try {
            $payload = $this->tokenVerifier->verifyPayload($token);

            return [
                'active' => ! $this->tokenBlacklist->isRevoked($token),
                'scope'  => implode(' ', $payload['scopes'] ?? []),
                'sub'    => $payload['sub'] ?? null,
                'exp'    => $payload['exp'] ?? null,
                'iat'    => $payload['iat'] ?? null,
                'type'   => $payload['type'] ?? null,
            ];
        } catch (Throwable) {
            return ['active' => false];
        }
    }
}
