<?php

declare(strict_types=1);

namespace Avax\Components\Identity\Tokens\System\Configuration\Assembly;

use Avax\Components\Identity\Tokens\System\Capabilities\JwtAuth\JwtAuth;
use Avax\Components\Identity\Tokens\System\Capabilities\JwtAuth\Signing\JwtSigner;
use Avax\Components\Identity\Tokens\System\Capabilities\JwtAuth\TokenBlacklist;
use Avax\Components\Identity\Tokens\System\Capabilities\JwtAuth\Verification\TokenVerifier;

/**
 * Assembles JwtAuth runtime instances from explicit signing configuration.
 */
final class JwtAuthGraph
{
    public static function hmac(string $secret, string $algo = 'HS256') : JwtAuth
    {
        $jwtSigner = new JwtSigner(secret: $secret, algo: $algo);

        return new JwtAuth(
            jwtSigner     : $jwtSigner,
            tokenVerifier : new TokenVerifier(jwtSigner: $jwtSigner),
            tokenBlacklist: new TokenBlacklist(),
        );
    }
}
