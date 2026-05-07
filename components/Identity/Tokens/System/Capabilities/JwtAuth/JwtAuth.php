<?php

declare(strict_types=1);

namespace Avax\Components\Identity\Tokens\System\Capabilities\JwtAuth;

use Avax\Components\Identity\Tokens\System\Capabilities\JwtAuth\Signing\JwtSigner;
use Avax\Components\Identity\Tokens\System\Capabilities\JwtAuth\Tokens\AccessToken;
use Avax\Components\Identity\Tokens\System\Capabilities\JwtAuth\Tokens\TokenPair;
use Avax\Components\Identity\Tokens\System\Capabilities\JwtAuth\Verification\TokenVerifier;
use RuntimeException;
use Throwable;

final class JwtAuth
{
    private static JwtSigner $jwtSigner;

    private static TokenVerifier $tokenVerifier;

    private static TokenBlacklist $tokenBlacklist;

    public static function configure(string $secret, string $algo = 'HS256') : void
    {
        self::$jwtSigner      = new JwtSigner($secret, $algo);
        self::$tokenVerifier  = new TokenVerifier(self::$jwtSigner);
        self::$tokenBlacklist = new TokenBlacklist();
    }

    public static function verify(string $token) : AccessToken
    {
        $verifier = self::verifier();

        if (self::$tokenBlacklist->isRevoked($token)) {
            throw new RuntimeException('Token has been revoked');
        }

        return $verifier->verify($token);
    }

    private static function verifier() : TokenVerifier
    {
        return self::$tokenVerifier ?? self::signer();
    }

    private static function signer() : JwtSigner
    {
        if (! isset(self::$jwtSigner)) {
            throw new RuntimeException('JwtAuth not configured. Call JwtAuth::configure() first.');
        }

        return self::$jwtSigner;
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

        self::$tokenBlacklist->revoke($refreshToken);

        $user = ['id' => $payload['sub']];

        return self::issue($user, $payload['scopes'] ?? []);
    }

    public static function revoke(string $token) : void
    {
        self::$tokenBlacklist->revoke($token);
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
                'active' => ! self::$tokenBlacklist->isRevoked($token),
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
