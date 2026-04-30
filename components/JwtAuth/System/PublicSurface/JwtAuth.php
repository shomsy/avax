<?php

declare(strict_types=1);

namespace Avax\Components\JwtAuth\System\PublicSurface;

use Avax\Components\JwtAuth\System\Capabilities\Signing\JwtSigner;
use Avax\Components\JwtAuth\System\Capabilities\Tokens\AccessToken;
use Avax\Components\JwtAuth\System\Capabilities\Tokens\RefreshToken;
use Avax\Components\JwtAuth\System\Capabilities\Tokens\TokenPair;
use Avax\Components\JwtAuth\System\Capabilities\Verification\TokenVerifier;
use RuntimeException;
use Throwable;

final class JwtAuth
{
    private static JwtSigner      $signer;
    private static TokenVerifier  $verifier;
    private static TokenBlacklist $blacklist;

    public static function configure(string $secret, string $algo = 'HS256') : void
    {
        self::$signer    = new JwtSigner($secret, $algo);
        self::$verifier  = new TokenVerifier(self::$signer);
        self::$blacklist = new TokenBlacklist();
    }

    public static function verify(string $token) : AccessToken
    {
        $verifier = self::verifier();

        if (self::$blacklist->isRevoked($token)) {
            throw new RuntimeException('Token has been revoked');
        }

        return $verifier->verify($token);
    }

    private static function verifier() : TokenVerifier
    {
        return self::$verifier ?? self::signer();
    }

    private static function signer() : JwtSigner
    {
        if (! isset(self::$signer)) {
            throw new RuntimeException('JwtAuth not configured. Call JwtAuth::configure() first.');
        }

        return self::$signer;
    }

    public static function refresh(string $refreshToken) : TokenPair
    {
        $verifier = self::verifier();
        $payload  = $verifier->verifyPayload($refreshToken);

        if (($payload['type'] ?? '') !== 'refresh') {
            throw new RuntimeException('Invalid token type for refresh');
        }

        if (($payload['exp'] ?? 0) < time()) {
            throw new RuntimeException('Refresh token has expired');
        }

        self::$blacklist->revoke($refreshToken);

        $user = ['id' => $payload['sub']];

        return self::issue($user, $payload['scopes'] ?? []);
    }

    public static function revoke(string $token) : void
    {
        self::$blacklist->revoke($token);
    }

    public static function issue(array $user, array $scopes = []) : TokenPair
    {
        $signer = self::signer();

        $now            = time();
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
            'sub'  => $user['id'],
            'jti'  => bin2hex(random_bytes(16)),
            'exp'  => $refreshExpires,
            'iat'  => $now,
            'type' => 'refresh',
        ];

        $accessToken  = $signer->sign($accessPayload);
        $refreshToken = $signer->sign($refreshPayload);

        return new TokenPair(
            accessToken : $accessToken,
            refreshToken: $refreshToken,
            expiresIn   : 900,
            tokenType   : 'Bearer',
        );
    }

    public static function introspect(string $token) : array
    {
        try {
            $verifier = self::verifier();
            $payload  = $verifier->verifyPayload($token);

            return [
                'active' => ! self::$blacklist->isRevoked($token),
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

class TokenBlacklist
{
    private static array $revoked = [];

    public function revoke(string $token) : void
    {
        self::$revoked[$token] = true;
    }

    public function isRevoked(string $token) : bool
    {
        return isset(self::$revoked[$token]);
    }

    public function clear() : void
    {
        self::$revoked = [];
    }
}